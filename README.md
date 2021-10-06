
# Парсер отзывов TrustPilot.com

![main](https://raw.githubusercontent.com/moss-maurice/trustpilot-parser/main/assets/images/main.png)

![PHP version](https://img.shields.io/badge/PHP->=v5.6-red.svg?php=5.6) ![qURL Library](https://img.shields.io/badge/qURL->=v0.1.1-green.svg?qURL=0.1.1)

## Для чего это надо?
Данный проект реализован для быстрого парсинга всех отзывов с портала [trustpilot.com](https://trustpilot.com/) для одного из сайтов. В результате будет создан дамп SQL в каталоге `export`. Таблица БД из дампа имеет следующую структуру:

![structure](https://raw.githubusercontent.com/moss-maurice/trustpilot-parser/main/assets/images/structure.png)

Для запуска, достаточно перейти в корневой каталог проекта и выполнить cli-команду:

```sh
root@localhost:~#: php import.php
```

Предварительно необходимо настроить файл-конфигурации из каталога `configs`.
