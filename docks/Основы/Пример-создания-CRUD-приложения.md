## Задача

По шагам создать каталог книг с авторами, жанрами и книгами. Должна быть авторизация для возможности редактирования. Книги и авторы - отношение многие ко многим, жанры и книги - отношение один ко многим. То есть, у книги может быть несколько авторов, а жанр может быть только один. Поиск книг по жанрам и авторам.

## Предварительно

Нужно произвести первоначальную настройку среды. Для этого надо следовать документации по настройке окружения.

## Инициализация проекта

Для инициализации нового проекта нужно выполнить команду:

```bash
pmng np libProject
```

Это создаст директорию /var/www/libProject и там инициализирует микросервис web, который получает HTTP запросы от пользователя и трансформирует их в запросы к микросервису. Для установки микросервиса библиотеки нужно выполнить команду:

```bash
pmng ns libProject/library
```

В директорию /var/www/libProject/library будет установлен новый микросервис, в котором и будет содержаться код, относящийся к нашей библиотеке. По пути будут заданы вопросы: какое название проекта должно быть и какую базу данных использовать. Имя проекта должно быть уникальным внутри одного окружения docker.

После этого нужно создать базу данных командой:

```bash
pmng createdb library
```

Это создаст базу данных с именем library; название должно совпадать с тем, которое было указано в запросе имени БД при создании микросервиса. Дальше пути будут указываться относительно /var/www/libProject.

## Настройка сервиса аутентификации

Это подробно описано в статье [Настройка сервиса аутентификации](../Документация/Аутентификация-и-авторизация#настройка-сервиса-аутентификации)

Конфигурирование

Для сервиса auth надо добавить в файл .env:

`SESSION_TTL=8192`

Это время жизни мандата в секундах, по его истечении надо будет аутентифицироваться заново. Перезапускаем контейнер backend командой:

```bash
vmng restart backend
```

## Авторы и жанры

Создаём метаданные для авторов командой:

```bash
pmng a libProject/library mk:metadata Author
```

Команда `pmng a` - это вызов artisan в конкретном микросервисе, в данном случае это libProject/library. Данная команда создаст файл метаданных library/app/Metadata/Author.php, который будет выглядеть примерно так:

```php
<?php

namespace App\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Metadata;

/**
* Class Author
* @package App\Metadata
*/
class Author extends Metadata
{

   /** @var string */
   protected string $label = 'Label';

   /** @var string */
   protected string $table = 'authors';

   /**
    * Author constructor.
    */
   public function __construct()
   {
       $this->data = [
           'id' => (new Field(FieldType::PK, '#'))
               ->setInChangeForm(false)
               ->setInCreateForm(false),
           'created_at' => (new Field(FieldType::DATETIME, 'Created'))
               ->setInChangeForm(false)
               ->setInCreateForm(false),
           'updated_at' => (new Field(FieldType::DATETIME, 'Modified'))
               ->setInChangeForm(false)
               ->setInCreateForm(false),
           'hash' => (new Field(FieldType::STRING, 'hash'))
               ->setRequired(true),
       ];
       parent::__construct();
   }

}
```

Это типовой шаблон метаданных, с которым мы будем дальше работать. Сущность автора может содержать имя автора, нам больше пока не нужно. Поэтому добавляем к стандартным полям в массиве $this->data следующее:

```php
'name' => new Field(FieldType::STRING, 'Name'),
```

Удобнее всего вставлять поля сущности после описания поля id (между id и created_at). Идентификатор идёт первым, а служебные поля в конце.

Дальше генерируем модель и миграцию:

```bash
pmng a libProject/library mk:model Author
pmng a libProject/library mk:migration Author
```

Для жанров заполняем всё точно так же, как и с авторами. Название жанра на английском языке Genre.

## Книги

Создаём метаданные книг:

```bash
pmng a libProject/library mk:metadata Book
```

Заполняем полями:

```php
'name' => new Field(FieldType::STRING, 'Name'),
'description' => new Field(FieldType::TEXT, 'Description'),
'genre_id' => (new Field(FieldType::RELATION, 'Genre'))
   ->setRelation('GenreRelation')
   ->setRequired(true),
```

Поле genre_id будет у нас ссылаться на жанр, так как он у нас один ко многим. Метод поля setRelation устанавливает связь между полем и отношением. Отношения описываются отдельно. Ниже, под инициализацией массива $this->data, нужно инициализировать массив отношений:

```php
$this->relations = [
   'GenreRelation' => new Relation(RelationType::BELONGS_TO, 'Genre'),
];
```

Массив отношений позволяет указать тип отношений и модель, с которой идёт связь. Отношение между книгами и авторами будет описано ниже.

Дальше инициализируем модель и миграцию как было выше:

```bash
pmng a libProject/library mk:model Book
pmng a libProject/library mk:migration Book
```

## Связь книги-авторы

Связь многие ко многим требует использования промежуточной таблицы. А для того, чтобы можно было без дополнительного кода искать по связям, нужно будет создать отдельную модель. Назовём её BookAuthor. Все команды дальше точно такие же, как и для других моделей, а метаданные нужно дополнить связями между другими таблицами:

```php
'book_id' => (new Field(FieldType::RELATION, 'Book'))
   ->setRequired(true)
   ->setRelation('BookRelation'),
'author_id' => (new Field(FieldType::RELATION, 'Author'))
   ->setRequired(true)
   ->setRelation('AuthorRelation'),
```

И массив $this->realtions:

```php
$this->relations = [
   'BookRelation' => new Relation(RelationType::BELONGS_TO, 'Book'),
   'AuthorRelation' => new Relation(RelationType::BELONGS_TO, 'Author'),
];
```

## Настройка прав доступа

Больше информации по настройке прав доступа можно найти здесь [Роли и пользователи](../Документация/Аутентификация-и-авторизация#роли-и-пользователи)

Для начала нам нужен пользователь с правами администратора. Для администратора создадим роль admin:

```bash
pmng a libProject/auth auth:create_role Admin admin 1
```

```bash
curl -iLXPOST http://localhost/auth/User/register -d '["Admin", "admin@mail.com", "password"]'
```

В данном случае права администратора добавляются каждому новому зарегистрировавшемуся пользователю, но для реальных задач так делать нельзя.

Так как мы используем доступ на чтение для всех, то в моделях на getItem и getItems нужно установить системную роль @all, которая работает для всех пользователей ресурса, даже не авторизованных в системе. Модели, которые нам нужны - это Book, Genre и Author:

```php
* @method-roles create admin
* @method-roles update admin
* @method-roles delete admin
* @method-roles getItem @all
* @method-roles getItems @all
* @method-roles getTree admin
```

## Запуск

Для начала запустим миграции:

```bash
pmng a libProject/library migrate
```

И перезапустим микросервис нашей библиотеки:

```bash
pmng ra libProject/library
```

Для проверки запускаем команду:

```bash
curl -iLXPOST http://localhost/library/Book/getItems
```

И увидим такое сообщение в ответ:

```
HTTP/1.1 200 OK
Server: nginx/1.17.10
Date: Tue, 08 Sep 2020 09:20:42 GMT
Content-Type: application/json
Transfer-Encoding: chunked
Connection: keep-alive
X-Powered-By: PHP/7.4.3
Access-Control-Allow-Origin: *
Access-Control-Allow-Headers: *
Cache-Control: private, must-revalidate
pragma: no-cache
expires: -1

{
    "uid": "82b22a4a-4a97-4ff5-9646-7f0400411884",
    "model": "Book",
    "action": "getItems",
    "data": {
        "items": [],
        "count": 0
    },
    "processTime": 0.026002883911132812,
    "hash": "66b458882c920674d1388a6bc646cfa93a1146c0d211c54a1e93334a25c35f5e",
    "method": 2
}
```

В поле “items” пустой массив, это значит, что у нас нет данных в базе. Наполнить базу можно тремя способами:

1. Создать миграцию
1. Использовать фронтенд
1. Наполнить обращениями к сервису прямыми запросами к API

Ниже будет рассмотрен последний вариант.

## Аутентификация сервисов

[Аутентификация сервисов](../Документация/Аутентификация-и-авторизация#аутентификация-сервисов)

Для того чтобы сервисы могли общаться друг с другом, нужно создать пользователя для каждого микросервиса, используемого в системе, кроме сервиса аутентификации и сервиса web. В этом поможет следующая команда artisan:

```bash
pmng a libProject/auth auth:create_service library ServerSecretPassword
```

Где ServerSecretPassword - это запись в .env APP_KEY сервиса library. У каждого микросервиса должен быть свой собственный ключ.

## Аутентификация и авторизация в системе из консоли

Аутентификация нужна для того, чтобы связать пользователя с аккаунтом, а авторизация - для того чтобы получить доступ к приватным точкам входа сервиса.

Более подробно о аутентификации и авторизации написано в статье [Аутентификация в системе из консоли](../Документация/Аутентификация-и-авторизация#аутентификация-в-системе-из-консоли)

После получения токена, для аутентификации в системе выполняем запрос:

```bash
curl -iLXPOST http://localhost/auth/User/login -d ‘{"email":"admin@mail.com","data":"{\"data\":\"oj4kVXrsdOyVjrLkZBEQgA==\",\"initVector\":\"b8280da8ecf74baaff9c8e21f7d52969\",\"salt\":\"6be25138e373a930\"}"}’
```

Для авторизации надо передать заголовок “Authorization: bearer”, который должен содержать токен, (генерация токена показана в [Аутентификация в системе из консоли](../Документация/Аутентификация-и-авторизация#аутентификация-в-системе-из-консоли) с помощью скрипта login.php):


```bash
curl -iLXPOST http://localhost/library/Book/getItems -H ‘Authorization: bearer {"email":"admin@mail.com","data":"{\"data\":\"oj4kVXrsdOyVjrLkZBEQgA==\",\"initVector\":\"b8280da8ecf74baaff9c8e21f7d52969\",\"salt\":\"6be25138e373a930\"}"}’
```

## Заполнение базы

Заполнить базу из консоли для авторов можно, обратившись к методу модели Author::create:

```bash
curl -iLXPOST http://localhost/library/Author/create -H ‘Authorization: bearer {"email":"admin@mail.com","data":"{\"data\":\"oj4kVXrsdOyVjrLkZBEQgA==\",\"initVector\":\"b8280da8ecf74baaff9c8e21f7d52969\",\"salt\":\"6be25138e373a930\"}"}’ -d '[{"name":"Pratchett"}]'
```

Также и для остальных моделей. Для создания связей книга-автор нужно использовать модель BookAuthor.

## Дополнительные настройки

Добавим в метаданные книги поле:

```php
'author' => (new Field(FieldType::RELATION, 'Author'))
   ->setRelation('AuthorRelation'),
```

И отношение:

```php
'AuthorRelation' => new Relation(RelationType::MANY_TO_MANY, 'Author', 'BookAuthor'),
```

Это позволит сортировать по автору. Сортировка по отношениям происходит по полю, указанному в свойстве $viewName метаданных, по-умолчанию это поле “name”. Смотрите запросы ниже.

Для авторов поле:

```php
'book' => (new Field(FieldType::RELATION, 'Book'))
   ->setRelation('BookRelation'),
```

Это поле не будет фигурировать в базе, так как оно ссылается на отношение многие-ко-многим. Оно описывается в массиве relations так:

```php
$this->relations = [
   'BookRelation' => new Relation(RelationType::MANY_TO_MANY, 'Book', 'BookAuthor'),
];
```

Для жанров поле:

```php
'book' => (new Field(FieldType::RELATION, 'Book'))
   ->setRelation('BookRelation'),
```

И отношения:

```php
$this->relations = [
   'BookRelation' => new Relation(RelationType::ONE_TO_MANY, 'Book'),
];
```

Отношения один-ко-многим также игнорируются для базы данных.

## Примеры запросов

Сортировка по жанрам:

```bash
curl -iLXPOST http://localhost/library/Book/getItems?_order_by=genre_id&_order=asc&_with=%5b"GenreRelation"%5d
```

Поиск по автору:

```bash
curl -iLXPOST http://localhost/library/Book/getItems?author=3&_with=%5b"AuthorRelation"%5d
```

То же самое по жанру.

Авторы с книгами:

```bash
curl -iLXPOST http://localhost/library/Author/getItems?_with=%5b"BookRelation"%5d
```

Жанры с книгами:

```bash
curl -iLXPOST http://localhost/library/Genre/getItems?_with=%5b"BookRelation"%5d
```
