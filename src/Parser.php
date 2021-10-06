<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

namespace mmaurice\parser\trustpilot;

use \mmaurice\parser\trustpilot\components\Registry;
use \KubAT\PhpSimple\HtmlDomParser;
use \mmaurice\qurl\Client;

class Parser
{
    protected $registry;
    protected $request;

    protected static $lastPageContent = [];

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;

        $this->request = (new Client)->request();
    }

    public function getLoot()
    {
        $nextLink = $this->registry->get('config')->root . $this->registry->get('config')->source;

        $counter = 0;

        do {
            $this->registry->get('timer')->start('page');

            $current = $nextLink;

            $resources = $this->parse($current);

            $nextLink = $resources['nextLink'];

            $dom = HtmlDomParser::str_get_html($resources['data']);

            $data = [];

            if (!is_null($resources['jsonData'])) {
                if (is_array($resources['jsonData'][0]->review) and !empty($resources['jsonData'][0]->review)) {
                    foreach ($resources['jsonData'][0]->review as $index => $review) {
                        $cardObject = $dom->find('.review-list .review-card', $index);
                        $replyObject = $cardObject->find('.review .review__company-reply', 0);

                        $row = [
                            'review_url' => $this->normalizeString(json_decode($cardObject->find('script', 0)->innertext)->socialShareUrl),
                            'review_date' => $this->normalizeDate($review->datePublished),
                            'review_title' => $this->normalizeString($review->headline),
                            'review_body' => $this->normalizeString($review->reviewBody),
                            'review_rate' => $review->reviewRating->ratingValue,
                            'review_lang' => $this->normalizeString($review->inLanguage),
                            'author_url' => $this->normalizeString($review->author->url),
                            'author_name' => $this->normalizeString($review->author->name),
                            'reply_date' => null,
                            'reply_title' => null,
                            'reply_body' => null,
                        ];

                        if (!is_null($replyObject)) {
                            $row['reply_date'] = $this->normalizeDate(json_decode($replyObject->find('.brand-company-reply__info .brand-company-reply__date script', 0)->innertext)->publishedDate);
                            $row['reply_title'] = $this->normalizeString($replyObject->find('.brand-company-reply__info .brand-company-reply__company', 0)->plaintext);
                            $row['reply_body'] = $this->normalizeString($replyObject->find('.brand-company-reply__content', 0)->innertext);
                        }

                        $data[] = $row;
                    }
                }
            }

            if (is_array($data) and !empty($data)) {
                $counter += count($data);

                $this->registry->get('dumper')->set("INSERT INTO `trustpilot_reviews`");
                $this->registry->get('dumper')->set("    (`" . implode("`, `", array_keys($data[0])) . "`)");
                $this->registry->get('dumper')->set("VALUES");

                foreach ($data as $index => $dataItem) {
                    $dataItem = array_map(function ($value) {
                        if (is_null($value) or empty($value)) {
                            return 'NULL';
                        }

                        return "'{$value}'";
                    }, $dataItem);

                    $this->registry->get('dumper')->set("    (" . implode(", ", array_values($dataItem)) . ")" . (($index !== (count($data) - 1)) ? ',' : ';'));
                }

                $this->registry->get('dumper')->set("");
            }

            $time = $this->registry->get('timer')->finish('page');

            $this->registry->get('timer')->destroy('page');

            $this->registry->get('logger')->set("Parse in {$time}", [
                'counter' => $counter,
                'url' => $current,
            ]);
        } while (!is_null($nextLink));
    }

    protected function normalizeString($string)
    {
        $string = html_entity_decode($string);
        $string = trim($string);
        $string = str_replace([
            '<br>',
            '<br/>',
            '<br />',
            PHP_EOL,
            chr(10),
            chr(13),
        ], ' ', $string);
        $string = $this->removeEmoji($string);
        $string = str_replace("'", "\\'", $string);
        $string = preg_replace('/[\s]+/im', ' ', $string);

        return $string;
    }

    protected function removeEmoji($string) {

        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);
    
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);
    
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);
    
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);
    
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);
    
        return $clear_string;
    }

    protected function normalizeDate($date)
    {
        if (preg_match('/([\d]{4})\-([\d]{2})\-([\d]{2})T([\d]{2})\:([\d]{2})\:([\d]{2})/im', $date, $matches)) {
            array_shift($matches);

            return "{$matches[0]}-{$matches[1]}-{$matches[2]} {$matches[3]}:{$matches[4]}:{$matches[5]}";
        }

        return '0000-00-00 00:00:00';
    }

    protected function parse($uri, array $getParams = [])
    {
        $results = [
            'data' => null,
            'jsonData' => null,
            'canonicalLink' => null,
            'nextLink' => null,
            'prevLink' => null,
        ];

        $response = $this->request->get([
            $uri,
            $getParams,
        ]);

        if (in_array($response->getResponseCode(), [200])) {
            $results['data'] = $response->getRawResponseBody();

            if (preg_match('/\<script(?:[^\>\<]*)data\-business\-unit\-json\-ld\>([^\<\>]*)\<\/script\>/ims', $response->getRawResponseBody(), $matches)) {
                array_shift($matches);

                $results['jsonData'] = json_decode(trim($matches[0]));
            }

            if (preg_match('/\<link\s+(?:rel\=\"canonical\")\s+(?:href\=\"([^\"]+)\")\s+\/\>/ims', $response->getRawResponseBody(), $matches)) {
                array_shift($matches);

                $results['canonicalLink'] = trim($matches[0]);
            }

            if (preg_match('/\<link\s+(?:rel\=\"next\")\s+(?:href\=\"([^\"]+)\")\s+\/\>/ims', $response->getRawResponseBody(), $matches)) {
                array_shift($matches);

                $results['nextLink'] = trim($matches[0]);
            }

            if (preg_match('/\<link\s+(?:rel\=\"prev\")\s+(?:href\=\"([^\"]+)\")\s+\/\>/ims', $response->getRawResponseBody(), $matches)) {
                array_shift($matches);

                $results['prevLink'] = trim($matches[0]);
            }
        }

        return $results;
    }
}
