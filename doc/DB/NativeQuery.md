# 数据库：原生 SQL 查询

- [简介](#introduction)
  - [运行原生的 SQL 查询](#running-queries)
  - [监听查询事件](#listening-for-query-events)
  - [数据库事务](#database-transactions)



<a name="introduction"></a>
## 简介

Laravel 的 原生 SQL 查询为创建和运行数据库查询提供了一个运行原生 SQL 的接口。



<a name="running-queries"></a>
## 运行原生 SQL 查询

一旦配置好数据库连接后，便可以使用 `DB` facade 运行查询。 `DB` facade 为每种类型的查询提供了方法： `select`，`update`，`insert`，`delete` 和 `statement`。

#### 运行 Select 查询

你可以使用 `DB` Facade 的 `select` 方法来运行基础的查询语句： 

    <?php

    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\DB;
    use App\Http\Controllers\Controller;

    class UserController extends Controller
    {
        /**
         * 显示应用程序中所有用户的列表
         *
         * @return Response
         */
        public function index()
        {
            $users = DB::select('select * from users where active = ?', [1]);

            return view('user.index', ['users' => $users]);
        }
    }

传递给 `select` 方法的第一个参数就是一个原生的 SQL 查询，而第二个参数则是需要绑定到查询中的参数值。通常，这些值用于约束 `where` 语句。参数绑定用于防止 SQL 注入。

`select` 方法将始终返回一个数组，数组中的每个结果都是一个= `StdClass` 对象，可以像下面这样访问结果值：

    foreach ($users as $user) {
        echo $user->name;
    }

#### 使用命名绑定

除了使用 `?` 表示参数绑定外，你也可以使用命名绑定来执行一个查询：

    $results = DB::select('select * from users where id = :id', ['id' => 1]);

#### 运行插入语句

可以使用 `DB` Facade 的 `insert` 方法来执行 `insert` 语句。与 `select` 一样，该方法将原生 SQL 查询作为其第一个参数，并将绑定数据作为第二个参数：

    DB::insert('insert into users (id, name) values (?, ?)', [1, 'Dayle']);

#### 运行更新语句

`update` 方法用于更新数据库中现有的记录。该方法返回受该语句影响的行数：

    $affected = DB::update('update users set votes = 100 where name = ?', ['John']);

#### 运行删除语句

`delete` 方法用于从数据库中删除记录。与 `update` 一样，返回受该语句影响的行数：

    $deleted = DB::delete('delete from users');

#### 运行普通语句

有些数据库语句不会有任何返回值。对于这些语句，你可以使用 `DB` Facade 的 `statement` 方法来运行：

    DB::statement('drop table users');

<a name="listening-for-query-events"></a>
## 监听查询事件

如果你想监控程序执行的每一个 SQL 查询，你可以使用 `listen` 方法。这个方法对于记录查询或调试非常有用。你可以在 [服务提供器](/docs/{{version}}/providers) 中注册你的查询监听器：

    <?php

    namespace App\Providers;

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        /**
         * 注册服务提供器
         *
         * @return void
         */
        public function register()
        {
            //
        }

        /**
         * 启动应用服务
         *
         * @return void
         */
        public function boot()
        {
            DB::listen(function ($query) {
                // $query->sql
                // $query->bindings
                // $query->time
            });
        }
    }

<a name="database-transactions"></a>
## 数据库事务

你可以使用 `DB` facade 的 `transaction` 方法在数据库事务中运行一组操作。如果事务的闭包 `Closure` 中出现一个异常，事务将会回滚。如果事务闭包 `Closure` 执行成功，事务将自动提交。一旦你使用了 `transaction` ， 就不再需要担心手动回滚或提交的问题：

    DB::transaction(function () {
        DB::table('users')->update(['votes' => 1]);

        DB::table('posts')->delete();
    });

#### 处理死锁

`transaction` 方法接受一个可选的第二个参数 ，该参数用来表示事务发生死锁时重复执行的次数。一旦定义的次数尝试完毕，就会抛出一个异常：

    DB::transaction(function () {
        DB::table('users')->update(['votes' => 1]);

        DB::table('posts')->delete();
    }, 5);

#### 手动使用事务

如果你想要手动开始一个事务，并且对回滚和提交能够完全控制，那么你可以使用 `DB` Facade 的 `beginTransaction` 方法：

    DB::beginTransaction();

你可以使用 `rollBack` 方法回滚事务：

    DB::rollBack();

最后，你可以使用 `commit` 方法提交事务：

    DB::commit();

> {tip} `DB` facade 的事务方法同样适用于 [查询构造器](/docs/{{version}}/queries) 和 [Eloquent ORM](/docs/{{version}}/eloquent) 。
