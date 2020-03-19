# 数据库：入门


- [简介](#introduction)
    - [配置](#configuration)
    - [读写分离](#read-and-write-connections)
    - [使用多个数据库连接](#using-multiple-database-connections)


<a name="introduction"></a>
## 简介

Laravel 能使用原生 SQL、[流畅的查询构造器](https://learnku.com/docs/laravel/6.0/queries) 和 [Eloquent ORM](https://learnku.com/docs/laravel/6.0/eloquent) 在各种数据库后台与数据库进行非常简单的交互。当前 Laravel 支持四种数据库:

<div class="content-list" markdown="1">
- MySQL
- PostgreSQL
- SQLite
- SQL Server
</div>

<a name="configuration"></a>
### 配置
数据库的配置文件放置在 `config/database.php` 文件中，你可以在此定义所有的数据库连接，并指定默认使用的连接。此文件内提供了大部分 Laravel 能支持的数据库配置示例。

默认情况下，Laravel 的示例 [环境配置](https://learnku.com/docs/laravel/6.0/configuration#environment-configuration) 使用了 [Laravel Homestead](https://learnku.com/docs/laravel/6.0/homestead)（这是一种小型虚拟机，能让你很方便地在本地进行 Laravel 的开发）。你可以根据本地数据库的需要修改这个配置。

#### SQLite 配置

使用类似 `touch database/database.sqlite` 之类命令创建一个新的 SQLite 数据库之后，可以使用数据库的绝对路径配置环境变量来指向这个新创建的数据库:


    DB_CONNECTION=sqlite
    DB_DATABASE=/absolute/path/to/database.sqlite
	
如果要开启 SQLite 连接的外键约束，您应该将 `foreign_key_constraints` 添加到 `config / database.php` 配置文件中：

    'sqlite' => [
        // ...
        'foreign_key_constraints' => true,
    ],

#### URLs 式配置
通常，数据库连接使用多个配置值，例如 `host`, `database`, `username`, `password` 等。这些配置值中的每一个都有其相应的环境变量。这意味着在生产服务器上配置数据库连接信息时，需要管理多个环境变量。

一些托管数据库提供程序（如 heroku ）提供单个数据库「URL」，该 url 在单个字符串中包含数据库的所有连接信息。示例数据库 URL 可能如下所示：

    mysql://root:password@127.0.0.1/forge?charset=UTF-8

这些url通常遵循标准模式约定：

    driver://username:password@host:port/database?options

为了方便起见，Laravel 支持这些 URLs ，作为使用多个配置选项配置数据库的替代方法。如果存在 `url ` （或相应的 `DATABASE_URL` 环境变量）配置选项，则将使用该选项提取数据库连接和凭据信息。

<a name="read-and-write-connections"></a>
### 读写分离

有时候你希望 SELECT 语句使用一个数据库连接，而 INSERT，UPDATE，和 DELETE 语句使用另一个数据库连接。在 Laravel 中，无论你是使用原生查询，查询构造器，或者是 Eloquent ORM，都能轻松的实现

为了弄明白读写分离是如何配置的，我们先来看个例子：

    'mysql' => [
        'read' => [
            'host' => [
                '192.168.1.1',
                '196.168.1.2',
            ],
        ],
        'write' => [
            'host' => [
                '196.168.1.3',
             ],
        ],
        'sticky'    => true,
        'driver'    => 'mysql',
        'database'  => 'database',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ],
注意在以上的例子中，配置数组中增加了三个键，分别是 `read`， `write` 和 `sticky`。 `read` 和 `write` 的键都包含一个键为 `host` 的数组。而 `read` 和 `write` 的其他数据库都在键为 `mysql` 的数组中。

如果你想重写主数组中的配置，只需要修改 `read` 和 `write` 数组即可。所以，这个例子中： `192.168.1.1`和`192.168.1.2` 将作为 「读」 连接主机，而 `192.168.1.3` 将作为 「写」 连接主机。这两个连接会共享 `mysql` 数组的各项配置，如数据库的凭据（用户名 / 密码），前缀，字符编码等。

####  `sticky` 选项
`sticky` 是一个 *可选值*，它可用于立即读取在当前请求周期内已写入数据库的记录。若 `sticky` 选项被启用，并且当前请求周期内执行过 「写」 操作，那么任何 「读」 操作都将使用 「写」 连接。这样可确保同一个请求周期内写入的数据可以被立即读取到，从而避免主从延迟导致数据不一致的问题。不过是否启用它，取决于应用程序的需求。

<a name="using-multiple-database-connections"></a>
### 使用多个数据库连接
当使用多个数据库连接时，你可以通过 `DB` Facade 的 `connection` 方法访问每一个连接。传递给 `connection`方法的参数 `name` 应该是 `config/database.php` 配置文件中 connections 数组中的一个值：

    $users = DB::connection('foo')->select(...);
	
你也可以使用一个连接实例上的 `getPdo` 方法访问底层的 PDO 实例：

    $pdo = DB::connection()->getPdo();

