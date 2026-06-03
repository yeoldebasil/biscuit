
# biscuit
biscuit - php-фреймворк, который:
- имеет удобные обертки для работы с типами данных 
- стремиться быть интуитивно понятным ~и не перегруженным~

(WIP)

## Пример использования

```php
use Yeoldebasil\Biscuit\{
  Http,
  Util
};

try {
  kick(
    root: '..',
    allowed_hosts: arr('localhost', '127.0.0.1')
  );

  http::respond(
    status: 200,
    headers: ['Content-Type' => 'text/plain'],
    body: 'Hello, world!'
  );
} catch (Throwable $t) {
  util::err($t);
  exit;
}
```


## Установка
- для корректной работы требуется php 8.4.1^, nginx и fastcgi, composer
- `composer init`, затем добавить в composer.json:
```json
"repositories": [{"type": "vcs", "url": "https://github.com/yeoldebasil/biscuit"}]
```
- `composer require yeoldebasil/biscuit`

## 

### Классы

#### Fmt

`log` последовательно выводит все входящие аргументы любого типа в виде строки 

#### Str

`Str` это класс для расширенной работы со строками, создается следующим образом:
```php
$str = str('Hello, world!');
```

`upper`, `lower` изменяют регистр :
```php
fmt::log($str->lower(), $str->upper());

-> 'hello, world!', 'HELLO, WORLD!'
```

`startsWith`, `endsWith` проверяют наличие префикса / суффикса в начале или конце строки соответственно:
```php
fmt::log( 
  $str->endsWith('world!'),
  $str->startsWith('goon baka baka 67 67')
);

-> true, false
```

`blank` проверяет, состоит ли строка только из пробелов

```php
$result = $str->blank()
  ? "Blank string"
  : null;

fmt::log($result);

-> null
```
#### Arr

`Arr` это класс для расширенной работы с массивами-списками, создается следующим образом:

```php
$cities = arr(
  'Москва',
  'Санкт-Петербург',
  'Ставрополь'
);

fmt::log( "Всего: {$cities->len()}", $cities );

->  'Всего: 3', [Москва, Санкт-Петербург, Ставрополь]
```

#### Map

`Map` это класс для расширенной работы со хеш-массивами (ключ: значение), создается следующим образом:

```php
$location = map(
  x: 345,
  y: -23,
  name: 'Pin'
);

fmt::log( "X: {$location->x}" );

->  'X: 345'
```

#### Env

`get` возвращает значение одной или всех переменных окружения 

```php
fmt::log( env::get('PATH') );

-> 'C:\Windows\system32;C:\Windows;C:\...'
```

#### Gen

`uuidv4` генерирует 36-значный UUID

```php
fmt::log( gen::uuidv4() );

-> '7f963aff-1919-4fd2-92bb-a420721adc01'
```

`hex` генерирует последовательность шестнадцатиричных значений в случайном порядке

```php
fmt::log( pwd::hex(16) );

-> 'b7841a0d1b1bac09'
```

`alnum` генерирует случайное значение в формате `A-z0-9`

`alnumu` генерирует случайное значение в формате `A-z0-9_`

```php
fmt::log( gen::alnum(10) );

-> '9gjEH6VW2q'
```

Также есть следующие методы для генерации в соответствующих форматах: 

`base58`, `base62`, `base64`, `base64url`

#### Http

`Http` захватывает свойства входящего соединения и дает функционал чтобы послать ответ. 

##### Свойства

`$ip` IP-адрес входящего соединения<br>
`$useragent` User Agent (урезан до 255 симв.)<br>
`$uri` URI входящего соединения<br>
`$method` HTTP-метод входящего соединения<br>
`$secure` Используется ли HTTPS

```php
fmt::log( 
  http::$ip,
  http::$useragent,
  http::$uri,
  http::$method
  http::$secure
);

-> '127.0.0.1', 'Mozilla 14.5...', '/index.php', 'GET', true
```

##### Методы

`respond` отвечает на входящий HTTP-запрос, не завершая работу скрипта

```php
http::respond(
  status: 200,
  headers: ['Content-Type' => 'text/plain'],
  body: 'Hello, world!'
);
```

#### Db

`Db` необходим для работы с базой данных

#### Util

`err` красиво показывает исключения
