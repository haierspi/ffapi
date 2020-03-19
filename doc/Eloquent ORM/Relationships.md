# Eloquent: 关联

- [简介](#introduction)
- [定义关联](#defining-relationships)
    - [一对一](#one-to-one)
    - [一对多](#one-to-many)
    - [一对多 (反向)](#one-to-many-inverse)
    - [多对多](#many-to-many)
    - [远程一对多](#has-many-through)
- [多态关联](#polymorphic-relations)
    - [一对一](#one-to-one-polymorphic-relations)
    - [一对多](#one-to-many-polymorphic-relations)
    - [多对多](#many-to-many-polymorphic-relations)
    - [自定义多态类型](#custom-polymorphic-types)
- [查询关联](#querying-relations)
    - [关联方法 Vs. 动态属性](#relationship-methods-vs-dynamic-properties)
    - [基于存在的关联查询](#querying-relationship-existence)
    - [基于不存在的关联查询](#querying-relationship-absence)
    - [关联数据计数](#counting-related-models)
- [预加载](#eager-loading)
    - [为预加载添加约束条件](#constraining-eager-loads)
    - [延迟预加载](#lazy-eager-loading)
- [插入 & 更新关联模型](#inserting-and-updating-related-models)
    - [ `save` 方法](#the-save-method)
    - [ `create` 方法](#the-create-method)
    - [更新 `belongs To` 关联](#updating-belongs-to-relationships)
    - [多对多关联](#updating-many-to-many-relationships)
- [更新父级时间戳](#touching-parent-timestamps)

<a name="introduction"></a>
## 简介

数据库表通常相互关联。 例如，一篇博客文章可能有许多评论，或者一个订单对应一个下单用户。Eloquent 让这些关联的管理和使用变得简单，并支持多种类型的关联：

- [一对一](#one-to-one)
- [一对多](#one-to-many)
- [多对多](#many-to-many)
- [远程一对多](#has-many-through)
- [一对一 (多态关联)](#one-to-one-polymorphic-relations)
- [一对多 (多态关联)](#one-to-many-polymorphic-relations)
- [多对多 (多态关联)](#many-to-many-polymorphic-relations)

<a name="defining-relationships"></a>
## 定义关联

 Eloquent 关联在 Eloquent 模型类中以方法的形式呈现. 如同 Eloquent 模型本身，关联也可以作为强大的 [查询语句构造器](/docs/{{version}}/queries) 使用, 提供了强大的链式调用和查询功能。例如，我们可以在 `posts` 关联的链式调用中附加一个约束条件：

    $user->posts()->where('active', 1)->get();

不过，在深入使用关联之前，让我们先学习如何定义每种关联类型。

<a name="one-to-one"></a>
### 一对一

一对一是最基本的关联关系。例如，一个 `User` 模型可能关联一个  `Phone` 模型。为了定义这个关联，我们要在 `User` 模型中写一个 `phone` 方法。在 `phone` 方法内部调用 `hasOne` 方法并返回其结果：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
         * 获取与用户关联的电话号码记录。
         */
        public function phone()
        {
            return $this->hasOne('App\Phone');
        }
    }

 `hasOne` 方法的第一个参数是关联模型的类名。一旦定义了模型关联，我们就可以使用 Eloquent 动态属性获得相关的记录。动态属性允许你访问关系方法就像访问模型中定义的属性一样：

    $phone = User::find(1)->phone;

 Eloquent 会基于模型名决定外键名称。在这种情况下，会自动假设 `Phone` 模型有一个 `user_id` 外键。如果你想覆盖这个约定，可以传递第二个参数给 `hasOne` 方法：

    return $this->hasOne('App\Phone', 'foreign_key');

另外，Eloquent 假设外键的值是与父级 `id` (或自定义 `$primaryKey` ) 列的值相匹配的。换句话说，Eloquent将会在 `Phone` 记录的  `user_id` 列中查找与用户表的 `id` 列相匹配的值。如果您希望该关联使用 `id` 以外的自定义键名，则可以给 `hasOne` 方法传递第三个参数：

    return $this->hasOne('App\Phone', 'foreign_key', 'local_key');

#### 定义反向关联

我们已经能从 `User` 模型访问到 `Phone` 模型了。现在，让我们再在 `Phone` 模型上定义一个关联，这个关联能让我们访问到拥有该电话的 `User` 模型。我们可以使用与 `hasOne` 方法对应的 `belongsTo` 方法来定义反向关联：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Phone extends Model
    {
        /**
         * 获得拥有此电话的用户。
         */
        public function user()
        {
            return $this->belongsTo('App\User');
        }
    }

在上面的例子中， Eloquent 会尝试匹配 `Phone` 模型上的 `user_id` 至 `User` 模型上的 `id` 。它是通过检查关系方法的名称并使用 `_id` 作为后缀名来确定默认外键名称的。但是，如果 `Phone` 模型的外键不是 `user_id` ，那么可以将自定义键名作为第二个参数传递给 `belongsTo` 方法：

    /**
     * 获得拥有此电话的用户。
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'foreign_key');
    }

如果父级模型没有使用 `id` 作为主键，或者是希望用不同的字段来连接子级模型，则可以通过给 `belongsTo` 方法传递第三个参数的形式指定父级数据表的自定义键：

    /**
     * 获得拥有此电话的用户。
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'foreign_key', 'other_key');
    }


<a name="one-to-many"></a>
### 一对多

『一对多』关联用于定义单个模型拥有任意数量的其它关联模型。例如，一篇博客文章可能会有无限多条评论。正如其它所有的 Eloquent 关联一样，一对多关联的定义也是在 Eloquent 模型中写一个方法：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Post extends Model
    {
        /**
         * 获取博客文章的评论
         */
        public function comments()
        {
            return $this->hasMany('App\Comment');
        }
    }

记住一点，Eloquent 将会自动确定 `Comment` 模型的外键属性。按照约定，Eloquent 将会使用所属模型名称的 『snake case』形式，再加上 `_id` 后缀作为外键字段。因此，在上面这个例子中，Eloquent 将假定 `Comment` 对应到 `Post` 模型上的外键就是 `post_id`。

一旦关系被定义好以后，就可以通过访问 `Post` 模型的 `comments` 属性来获取评论的集合。记住，由于 Eloquent 提供了『动态属性』 ，所以我们可以像访问模型的属性一样访问关联方法：

    $comments = App\Post::find(1)->comments;

    foreach ($comments as $comment) {
        //
    }

当然，由于所有的关联还可以作为查询语句构造器使用，因此你可以使用链式调用的方式，在 `comments` 方法上添加额外的约束条件：

    $comment = App\Post::find(1)->comments()->where('title', 'foo')->first();

正如 `hasOne` 方法一样，你也可以在使用 `hasMany` 方法的时候，通过传递额外参数来覆盖默认使用的外键与本地键：

    return $this->hasMany('App\Comment', 'foreign_key');

    return $this->hasMany('App\Comment', 'foreign_key', 'local_key');

<a name="one-to-many-inverse"></a>
### 一对多（反向）

现在，我们已经能获得一篇文章的所有评论，接着再定义一个通过评论获得所属文章的关联关系。这个关联是 `hasMany` 关联的反向关联，需要在子级模型中使用 `belongsTo` 方法定义它：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Comment extends Model
    {
        /**
         * 获取此评论所属文章
         */
        public function post()
        {
            return $this->belongsTo('App\Post');
        }
    }

这个关系定义好以后，我们就可以通过访问 `Comment` 模型的 `post` 这个『动态属性』来获取关联的 `Post` 模型了：

    $comment = App\Comment::find(1);

    echo $comment->post->title;

在上面的例子中，Eloquent 会尝试用 `Comment` 模型的 `post_id` 与 `Post` 模型的 `id` 进行匹配。默认外键名是 Eloquent 依据关联名，并在关联名后加上 `_` 再加上主键字段名作为后缀确定的。当然，如果 `Comment` 模型的外键不是 `post_id`，那么可以将自定义键名作为第二个参数传递给 `belongsTo` 方法：

    /**
     * 获得评论所属的文章
     */
    public function post()
    {
        return $this->belongsTo('App\Post', 'foreign_key');
    }

如果父级模型没有使用 `id` 作为主键，或者是希望用不同的字段来连接子级模型，则可以通过给 `belongsTo` 方法传递第三个参数的形式指定父级数据表的自定义键：

    /**
     * 获取此评论所属的文章
     */
    public function post()
    {
        return $this->belongsTo('App\Post', 'foreign_key', 'other_key');
    }


<a name="many-to-many"></a>
### 多对多

多对多关联比 `hasOne` 和 `hasMany` 关联稍微复杂些。举个例子，一个用户可以拥有很多种角色，同时这些角色也被其他用户共享。例如，许多用户可能都有 「管理员」 这个角色。要定义这种关联，需要三个数据库表： `users`，`roles` 和  `role_user`。 `role_user` 表的命名是由关联的两个模型按照字母顺序来的，并且包含了 `user_id` 和 `role_id` 字段。

多对多关联通过调用 `belongsToMany` 这个内部方法返回的结果来定义，例如，我们在 `User` 模型中定义 `roles` 方法：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
         * 用户的角色
         */
        public function roles()
        {
            return $this->belongsToMany('App\Role');
        }
    }

一旦关联关系被定义后，你可以通过 `roles` 动态属性获取用户角色：

    $user = App\User::find(1);

    foreach ($user->roles as $role) {
        //
    }

当然，像其它所有关联模型一样，你可以使用 `roles` 方法，利用链式调用对查询语句添加约束条件：

    $roles = App\User::find(1)->roles()->orderBy('name')->get();

正如前面所提到的，为了确定关联连接表的表名，Eloquent 会按照字母顺序连接两个关联模型的名字。当然，你也可以不使用这种约定，传递第二个参数到 `belongsToMany` 方法即可：

    return $this->belongsToMany('App\Role', 'role_user');

除了自定义连接表的表名，你还可以通过传递额外的参数到 `belongsToMany` 方法来定义该表中字段的键名。第三个参数是定义此关联的模型在连接表里的外键名，第四个参数是另一个模型在连接表里的外键名：

    return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');

#### 定义反向关联

要定义多对多的反向关联，你只需要在对关联模型中调用 `belongsToMany` 方法即可。我们在 `Role` 模型中定义 `users` 方法：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        /**
         * 拥有此角色的用户
         */
        public function users()
        {
            return $this->belongsToMany('App\User');
        }
    }

如你所见，除了引入模型为 `App\User` 外，其它与在 `User` 模型中定义的完全一样。由于我们重用了 `belongsToMany` 方法，自定义连接表表名和自定义连接表里的键的字段名称在这里同样适用。

#### 获取中间表字段

你已经学到，多对多的关联需要一个中间表支持，Eloquent 提供了一些有用的方法来和这张表进行交互。例如，假设我们的 `User` 对象关联了多个 `Role` 对象。在获得这些关联对象后，可以使用模型的 `pivot` 属性访问中间表的数据：

    $user = App\User::find(1);

    foreach ($user->roles as $role) {
        echo $role->pivot->created_at;
    }

需要注意的是，我们获取的每个 `Role` 模型对象，都会被自动赋予  `pivot` 属性，它代表中间表的一个模型对象，并且可以像其他的 Eloquent 模型一样使用。

默认情况下， `pivot` 对象只包含两个关联模型的主键，如果你的中间表里还有其他额外字段，你必须在定义关联时明确指出：

    return $this->belongsToMany('App\Role')->withPivot('column1', 'column2');

如果你想让中间表自动维护 `created_at` 和 `updated_at` 时间戳，那么在定义关联时附加上 `withTimestamps` 方法即可：

    return $this->belongsToMany('App\Role')->withTimestamps();

#### 自定义 `pivot` 属性名称

如前所述，来自中间表的属性可以使用 `pivot` 属性访问。但是，你可以自由定制此属性的名称，以便更好的反应其在应用中的用途。

例如，如果你的应用中包含可能订阅的用户，则用户与博客之间可能存在多对多的关系。如果是这种情况，你可能希望将中间表访问器命名为  `subscription` 取代 `pivot`。这可以在定义关系时使用 `as` 方法完成：

    return $this->belongsToMany('App\Podcast')
                    ->as('subscription')
                    ->withTimestamps();

一旦定义完成，你可以使用自定义名称访问中间表数据：

    $users = User::with('podcasts')->get();

    foreach ($users->flatMap->podcasts as $podcast) {
        echo $podcast->subscription->created_at;
    }



#### 通过中间表列过滤关系

在定义关系时，你还可以使用 `wherePivot` 和 `wherePivotIn` 方法来过滤 `belongsToMany` 返回的结果：

    return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

    return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);


#### 定义自定义中间表模型

如果你想定义一个自定义模型来表示关联关系中的中间表，可以在定义关联时调用 `using` 方法。所有自定义中间表模型都必须扩展自 `Illuminate\Database\Eloquent\Relations\Pivot` 类。例如，
我们在写 `Role` 模型的关联时，使用自定义中间表模型 `UserRole`：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        /**
         * 获得此角色下的用户。
         */
        public function users()
        {
            return $this->belongsToMany('App\User')->using('App\UserRole');
        }
    }

当定义 UserRole 模型时，我们要扩展 `Pivot` 类：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Relations\Pivot;

    class UserRole extends Pivot
    {
        //
    }

<a name="has-many-through"></a>
### 远程一对多

「远程一对多」关联提供了方便、简短的方式通过中间的关联来获得远层的关联。例如，一个 `Country` 模型可以通过中间的 `User` 模型获得多个 `Post` 模型。在这个例子中，你可以轻易地收集给定国家的所有博客文章。让我们来看看定义这种关联所需的数据表：

    countries
        id - integer
        name - string

    users
        id - integer
        country_id - integer
        name - string

    posts
        id - integer
        user_id - integer
        title - string

虽然 `posts` 表中不包含 `country_id` 字段，但 `hasManyThrough` 关联能让我们通过 `$country->posts` 访问到一个国家下所有的用户文章。为了完成这个查询，Eloquent 会先检查中间表 `users` 的 `country_id` 字段，找到所有匹配的用户 ID 后，使用这些 ID，在 `posts` 表中完成查找。 

现在，我们已经知道了定义这种关联所需的数据表结构，接下来，让我们在 `Country` 模型中定义它：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Country extends Model
    {
        /**
         * 获得某个国家下所有的用户文章。
         */
        public function posts()
        {
            return $this->hasManyThrough('App\Post', 'App\User');
        }
    }

`hasManyThrough` 方法的第一个参数是我们最终希望访问的模型名称，而第二个参数是中间模型的名称。

当执行关联查询时，通常会使用 Eloquent 约定的外键名。如果你想要自定义关联的键，可以通过给 `hasManyThrough` 方法传递第三个和第四个参数实现，第三个参数表示中间模型的外键名，第四个参数表示最终模型的外键名。第五个参数表示本地键名，而第六个参数表示中间模型的本地键名：

    class Country extends Model
    {
        public function posts()
        {
            return $this->hasManyThrough(
                'App\Post',
                'App\User',
                'country_id', // 用户表外键...
                'user_id', // 文章表外键...
                'id', // 国家表本地键...
                'id' // 用户表本地键...
            );
        }
    }

<a name="polymorphic-relations"></a>
## 多态关联

多态关联允许一个模型在单个关联上属于多个其他模型。

<a name="one-to-one-polymorphic-relations"></a>
### 一对一 (多态关联)

#### 数据表结构

一对一的多态关联类似于简单的一对一关联，但目标模型可以在单个关联上属于多个其他模型。例如，博客文章和用户可以共享与图像模型的多态关联，使用一对一的多态关联，您可以用一个 `images` 表同时满足这两个使用场景。让我们来看看构建这种关联所需的数据表结构：

    posts
        id - integer
        title - string
        body - text

    users
        id - integer
        title - string
        url - string

    images
        id - integer
        body - text
        imageable_id - integer
        imageable_type - string

`images` 表中有两个需要注意的重要字段 `imageable_id` 和 `imageable_type`。`imageable_id` 用来保存文章或者用户的 ID 值，而 `imageable_type` 用来保存所属模型的类名。Eloquent 使用 `imageable_type` 来决定我们访问关联模型时，要返回的父模型的「类型」。

#### 模型结构

接下来，我们来看看创建这种关联所需的模型定义：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Image extends Model
    {
        /**
         * 获得拥有此图像的模型。
         */
        public function imageable()
        {
            return $this->morphTo();
        }
    }

    class Post extends Model
    {
        /**
         * 获得此文章的图像。
         */
        public function image()
        {
            return $this->morphOne('App\Image', 'imageable');
        }
    }

    class User extends Model
    {
        /**
         * 获得此用户的图像。
         */
        public function image()
        {
            return $this->morphOne('App\Image', 'imageable');
        }
    }

#### 获取多态关联

一旦你的数据库表准备好、模型定义完成后，就可以通过模型来访问关联了。例如，我们只要简单地使用 `image` 动态属性，就可以获得某篇文章的图像：

    $post = App\Post::find(1);

    $image = $post->image;

你也可以在多态模型上，通过访问调用了 `morphTo` 的关联方法获得多态关联的拥有者。在当前例子中，是 `Image` 模型的 `imageable` 方法。所以，我们可以使用动态属性来访问这个方法：

    $image = App\Image::find(1);

    $imageable = $image->imageable;

`Image` 模型的 `imageable` 关联会返回 `Post` 或者 `User` 实例，这取决于图像所属的模型类型。

<a name="one-to-many-polymorphic-relations"></a>
### 一对多 (多态关联)

#### 数据表结构

一对多的多态关联类似于简单的一对多关联，但目标模型可以在单个关联上属于多个其他模型。例如，您的应用程序的用户可以「评论」文章和视频，使用一对多的多态关联，您可以用一个 `comments` 表同时满足这两个使用场景。让我们来看看构建这种关联所需的数据表结构：

    posts
        id - integer
        title - string
        body - text

    videos
        id - integer
        title - string
        url - string

    comments
        id - integer
        body - text
        commentable_id - integer
        commentable_type - string

`comments` 表中有两个需要注意的重要字段 `commentable_id` 和 `commentable_type`。`commentable_id` 用来保存文章或者视频的 ID 值，而 `commentable_type` 用来保存所属模型的类名。Eloquent 使用 `commentable_type` 来决定我们访问关联模型时，要返回的父模型的「类型」。

#### 模型结构

接下来，我们来看看创建这种关联所需的模型定义：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Comment extends Model
    {
        /**
         * 获得拥有此评论的模型。
         */
        public function commentable()
        {
            return $this->morphTo();
        }
    }

    class Post extends Model
    {
        /**
         * 获得此文章的所有评论。
         */
        public function comments()
        {
            return $this->morphMany('App\Comment', 'commentable');
        }
    }

    class Video extends Model
    {
        /**
         * 获得此视频的所有评论。
         */
        public function comments()
        {
            return $this->morphMany('App\Comment', 'commentable');
        }
    }

#### 获取多态关联

一旦你的数据库表准备好、模型定义完成后，就可以通过模型来访问关联了。例如，我们只要简单地使用 `comments` 动态属性，就可以获得某篇文章下的所有评论：

    $post = App\Post::find(1);

    foreach ($post->comments as $comment) {
        //
    }

你也可以在多态模型上，通过访问调用了 `morphTo` 的关联方法获得多态关联的拥有者。在当前场景中，就是 `Comment` 模型的 `commentable` 方法。所以，我们可以使用动态属性来访问这个方法：

    $comment = App\Comment::find(1);

    $commentable = $comment->commentable;

`Comment` 模型的 `commentable` 关联会返回 `Post` 或者 `Video` 实例，这取决于评论所属的模型类型。


<a name="many-to-many-polymorphic-relations"></a>
### 多对多 (多态关联)

#### 数据表结构

「多对多」的多态关联比「一对一」和「一对多」的多态关联稍微复杂一些。举例而言，一个 `Post` 和 `Video` 模型共享一个 `Tag` 模型。使用多对多的多态关联将允许你去有一个在 `Video` 和 `Post` 之间共同使用一个唯一的标签列表。首先，让我们来看一下数据表结构：

```
posts
	id - integer
	name -string
	
videos
	id - integer
	name - string
	
tags
	id - integer
	name - string
	
taggables
	tag_id - integer
	taggable_id - integer
	taggable_type - string
```

#### 模型结构

接下来，我们要在模型上定义关联关系。 `Post` 和 `Video` 模型会有一个共同的 `tags` 方法，它们会被基础的模型的 `morphToMany` 方法调用：

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * 获取文章标签
     */
    public function tags()
    {
        return $this->morphToMany('App\Tag', 'taggable');
    }
}
```

#### 定义反向关联的关系

接下来，在 `Tag` 模型中，你应该定义一个关联到其它模型的方法。比方说，`posts` 方法和 `videos` 方法：

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * 获取拥有这个标签的文章
     */
    public function posts()
    {
        return $this->morphedByMany('App\Post', 'taggable');
    }

    /**
     *获取拥有这个标签的视频
     */
    public function videos()
    {
        return $this->morphedByMany('App\Video', 'taggable');
    }
}
```

#### 获取关联

当你的数据库表和模型定义完成之后，你可以使用 `tags` 动态属性，就可以通过你的模型去获取相应的关联关系了：

```php
$post = App\Post::find(1);

foreach ($post->tags as $tag) {
	//
}
```

你可以通过 `morphedByMany` 方法读取多态关联的所属模型。在我们的案例中，那就是定义在 `Tag` 模型上的  `posts` 或 `videos` 方法。所以，你可以把这些方法当做动态属性进行加载：

```php
$tag = App\Tag::find(1);

foreach ($tag->videos as $video) {
	//
}
```


<a name="custom-polymorphic-types"></a>
### 自定义多态类型

默认，Laravel 会使用完全限定类名作为关联模型保存在多态模型上的类型字段值。比如，在上面的例子中，`Comment` 属于 `Post` 或者 `Video`，那么 `commentable_type`的默认值对应地就是 `App\Post` 和 `App\Video`。但是，你可能希望将数据库与程序内部结构解耦。那样的话，你可以定义一个「多态映射表」来指示 Eloquent 使用每个模型自定义类型字段名而不是类名：

    use Illuminate\Database\Eloquent\Relations\Relation;

    Relation::morphMap([
        'posts' => 'App\Post',
        'videos' => 'App\Video',
    ]);

你可以在 `AppServiceProvider` 中的 `boot` 函数中使用 `Relation::morphMap` 方法注册「多态映射表」，或者使用一个独立的服务提供者注册。


<a name="querying-relations"></a>
## 查询关联

当所有类型的模型关联关系通过方法定义之后，你就可以通过这些方法读取对应的关联关系，而无需执行对应的关联查询语句。除了这些，所有定义在 `Eloquent` 上的关联关系也同时适用于 [查询构建器](/docs/{{version}}/queries)，在最后执行在数据库查询语句之前，允许你执行链式操作对你的关联查询条件进行约束。

举例而言，想象一个博客系统，其中，`User` 模型有多个关联的 `Post` 模型：

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 获取用户的所有文章
     */
    public function posts()
    {
        return $this->hasMany('App\Post');
    }
}
```

你可以为查寻的 `posts` 关联查询添加额外的限制，就像这样：

```php
$user = App\User::find(1);

$user->posts()->where('active', 1)->get();
```

你可以在关联方法上使用所有的 [查询构建器](/docs/{{version}}/queries) ，所以，你有必要仔细学习一下查询构建器的文档中涉及到的可用的方法。

<a name="relationship-methods-vs-dynamic-properties"></a>
### 关联关系与动态属性

如果你不需要在 `Eloquent` 模型查询上添加额外约束，你可以像使用属性一样使用关联关系。那我们继续使用 `User` 和 `Post` 进行举例，如下所示：

```php
$user = App\User::find(1);

foreach ($user->posts as $post) {
    //
}
```

动态属性就像「预加载」，也就是说当你确实会使用到它们的使用，它会自动进行加载。由于这一特性，开发者经常会使用到 [预加载](#eager-loading) 去实现那些在获取到模型后必须要加载的关联模型。预加载对 那些在模型上定了关联关系 SQL 查询上有了性能的提升，它们对 SQL 查询语句的执行次数有了明显的减少（也就是 n + 1性能问题）。

<a name="querying-relationship-existence"></a>
### 查询关联关系是否存在
当通过模型获取数据时，你可能希望限制在一个已存在的关系上。比如说，你想要获取至少包含一条评论的博客文章。你就应该这样做，使用针对关联关系的 `has` and `orHas` 方法：

```php
// 获取至少有一条评论的文章
$posts = App\Post::has('comments')->get();
```

你还可以为这个查询指定运算符：

```php
// 获取至少有3
$posts = App\Post::has('comments', '>=', 3)->get();
```

`has` 方法也可可以接受以 「.」 点形式的嵌套加载。举例来说，你要获取到至少包含一条评论和投票的博客：

```php
// 获取至少有一条评论的文章，并加载评论的投票信息
$posts = App\Post::has('comments.votes')->get();
```

如果你想要做更多特性，你还可以使用 `whereHas` 和 `
orWhereHas` 方法，在方法中，你可以指定 「where」 条件在你的 `has` 语句之中。这些方法允许你在关联查询之中添加自定义的条件约束，比如检查评论的内容：

```php
// 获取所有至少有一条评论的文章且评论内容以 foo 开头
$posts = App\Post::whereHas('comments', function ($query) {
    $query->where('content', 'like', 'foo%');
})->get();
```

<a name="querying-relationship-absence"></a>
### 查询不存在的关联关系
当你获取到数据时，你可能还想要筛选出不包含关联关系的数据。举例来说，你想要获取不包含任何评论的文章。那么你应该这样做，使用模型的 `doesntHave` 和 `orDoesntHave` 方法：

```php
$posts = App\Post::doesntHave('comments')->get();
```

如果你想要定制化，你可以使用 `whereDoesntHave` 和 `orWhereDoesntHave` 方法，在回调方法之中，定制 `where` 条件等。这些方法允许你自定义对关联关系的约束，就比如说检查评论内容：

```php
$posts = App\Post::whereDoesntHave('comments', function ($query) {
    $query->where('content', 'like', 'foo%');
})->get();
```

你可能要使用 「.」 点符号去执行一个嵌套的加载。举例来说，如下语句，将会获取到所有文章，同时加载了那些没有被禁用的用户的文章评论：

```php
$posts = App\Post::whereDoesntHave('comments.author', function ($query) {
	$query->where('banned', 1);
})->get();
```

<a name="counting-related-models"></a>
### 关联计数

当你只是需要对关联关系进行计数，并不需要这些数据时，你可以使用 `withCount` 方法去实现，它将会以 `{relation}_count` 形式出现在的模型上。举例而言：

```php
$posts = App\Post::withCount('comments')->get();

foreach ($posts as $post) {
	echo $post->comments_count;
}
```

你可以同时获取多个关联计数，同时还可以对关联计数添加条件约束：

```php
$posts = App\Post::withCount(['votes', 'comments' => function ($query) {
	$query->where('content', 'like', 'foo%');
}])->get();

echo $posts[0]->votes_count;
echo $posts[0]->comments_count;
```
当然，允许你定义关联计数的别名，也允许你对相同的关联关系进行多次计数，如下所示：

```
$posts = App\Post::withCount([
	'comments',
	'comments as pending_comments_count' => function ($query) {
		$query->where('approved', false);
	}
])->get();

echo $posts[0]->comments_count;

echo $posts[0]->pending_comments_count;
```

如果你需要同时使用 `select` 和 `withCount` ，一定要确保在 `select` 之后调用 `withCount` ：
```
$query = App\Post::select(['title', 'body'])->withCount('comments');

echo $posts[0]->title;
echo $posts[0]->body;
echo $posts[0]->comments_count;
```

<a name="eager-loading"></a>
## 预加载

当通过动态属性的方法去加载关联数据时，它已经是在 「预加载」 了。也就是说，当你在未使用到该关联数据时，它其实是并没有查询数据的。然而， 当你在查询父级模型时， Eloquent 允许预加载关联数据，预加载避免了 N + 1 查询问题。为了说明 N + 1 的查询问题，思考 `Book` 模型关联了  `Auhtor` ：

```php
 <?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /**
     * 获取作者
     */
    public function author()
    {
        return $this->belongsTo('App\Author');
    }
}
```

现在，让我们读取作者：

```php
$books = App\Book::all();

foreach ($books as $book) {
	echo $book->author->name;
}
```

这个循环会执行一条语句去数据库查询所有的书籍，然后为每一本书执行一条语句去获取作者。所以，当我们有 25 本书的时候，这个循环将会产生 26 条语句：1条语句读取书籍数据，另外25 条语句获取每本书籍的作者。

感谢天，感谢地，感谢伟大的预加载。我们可以使用预加载把这些操作步骤降低为 2 条查询语句。你可以使用 `with` 方法加载指定的关联数据。

```php
$books = App\Book::with('author')->get();

foreach ($books as $book) {
		echo $book->author->name;
}
```

在这次执行中，仅产生了两条语句：

```
select * from books

select * from authors where id in (1, 2, 3, 4, 5, ...)
```

#### 多关联关系的预加载

曾几何时，你也许需要加载一些不同的关联数据在同一次的操作中。如今，你可以使用 `with` 方法，把不同的关联数据当做参数传递即可。如下所示：

```php
$books = App\Book::with(['author', 'publisher'])->get();
```

#### 嵌套式的预加载

实现预加载也很简单，你可以使用 「.」 点语法去实现。譬如，我们需要加载所有的书籍，并且包含作者以及作者的联系方式。那么我们就可以这样写：

```php
$books = App\Book::with('author.contacts')->get();
```

#### 指定特定列的预加载

也许你需要加载的关联数据的字段并不多。Eloquent 允许你在加载关联数据时指定字段，就像这样：

```php
$users = App\Book::with('author:id,name')->get();
```

> {note} 当你使用这个特性时，你应该永远将 `id` 包含进来。

<a name="constraining-eager-loads"></a>
### 为预加载添加约束条件

有时，你也许希望预加载一个关联关系，但是你又希望对关联的数据进行限制，那么你可以这样：

```php
$users = App\User::with(['posts' => function ($query) {
		$query->where('title', 'like', '%first%');
}])->get();
```

在上面例子中，Eloquent 将加载所有 `title` 列包含 `first` 关键字的文章。除此以外，你还可以使用 [查询构建器]((/docs/{{version}}/queries) 的方法去自定义预加载的操作。

```php
$users = App\User::with(['posts' => function ($query) {
		$query->orderBy('created_at', 'desc');
}])->get();
```

<a name="lazy-eager-loading"></a>
### 延迟预加载

有可能你还希望在模型加载完成后在进行预加载。举例来说，如果你想要动态的加载关联数据，那么 `load` 方法对你来说会非常有用：

```php
$books = App\Book::all();

if ($someCondition) {
		$books->load('author', 'publisher');
}
```

如果你想要在预加载的查询语句中进行条件约束，你可以通过数组的形式去加载，键为对应的关联关系，值为 `Closure` 闭包函数，该闭包的参数为一个 `query` 实例：

```php
$books->load(['author' => function ($query) {
		$query->orderBy('published_date', 'asc');
}]);
```

当关联关系没有被加载时，你可以使用使用 `loadMissing` 方法：

```
public function format(Book $book)
{
		$book->loadMissing('author');

		return [
				'name' => $book->name,
				'author' => $book->author->name
		];
}
```



<a name="inserting-and-updating-related-models"></a>
## 插入 & 更新关联模型

<a name="the-save-method"></a>
### 保存方法

Eloquent 为新模型添加关联提供了便捷的方法。例如，也许你需要添加一个新的 `Comment` 到一个 `Post` 模型中。你不用在 `Comment`中手动设置 `post_id` 属性, 就可以直接使用关联模型的 `save` 方法将 `Comment` 直接插入：

    $comment = new App\Comment(['message' => 'A new comment.']);

    $post = App\Post::find(1);

    $post->comments()->save($comment);

需要注意的是，我们并没有使用动态属性的方式访问 `comments` 关联。相反，我们调用 `comments` 方法来获得关联实例。`save` 方法将自动添加适当的 `post_id` 值到 `Comment` 模型中。

如果你需要保存多个关联模型，你可以使用 `saveMany` 方法：

    $post = App\Post::find(1);

    $post->comments()->saveMany([
        new App\Comment(['message' => 'A new comment.']),
        new App\Comment(['message' => 'Another comment.']),
    ]);

<a name="the-create-method"></a>
### 新增方法

除了 `save` 和 `saveMany` 方法外，你还可以使用 `create` 方法。它接受一个属性数组，同时会创建模型并插入到数据库中。 还有， `save` 方法和 `create` 方法的不同之处在于， `save` 方法接受一个完整的 Eloquent 模型实例，而 `create` 则接受普通的 PHP 数组:

    $post = App\Post::find(1);

    $comment = $post->comments()->create([
        'message' => 'A new comment.',
    ]);

> {tip} 在使用 `create` 方法前，请务必确保查看过本文档的 [批量赋值](/docs/{{version}}/eloquent#mass-assignment) 章节。

你还可以使用 `createMany` 方法去创建多个关联模型：

    $post = App\Post::find(1);

    $post->comments()->createMany([
        [
            'message' => 'A new comment.',
        ],
        [
            'message' => 'Another new comment.',
        ],
    ]);

<a name="updating-belongs-to-relationships"></a>
### 更新 `belongsTo` 关联

当更新 `belongsTo` 关联时，可以使用 `associate` 方法。此方法将会在子模型中设置外键：

    $account = App\Account::find(10);

    $user->account()->associate($account);

    $user->save();

当移除 `belongsTo` 关联时，可以使用 `dissociate` 方法。此方法会将关联外键设置为 `null`:

    $user->account()->dissociate();

    $user->save();

<a name="default-models"></a>
#### 默认模型

`belongsTo` 关系允许你指定默认模型，当给定关系为 `null` 时，将会返回默认模型。 这种模式被称作 [Null 对象模式](https://en.wikipedia.org/wiki/Null_Object_pattern) ，可以减少你代码中不必要的检查。在下面的例子中，如果发布的帖子没有找到作者， `user` 关系会返回一个空的 `App\User` 模型：

    /**
     * 获取帖子的作者。
     */
    public function user()
    {
        return $this->belongsTo('App\User')->withDefault();
    }

如果需要在默认模型里添加属性， 你可以传递数组或者回调方法到 `withDefault` 中：

    /**
     * 获取帖子的作者。
     */
    public function user()
    {
        return $this->belongsTo('App\User')->withDefault([
            'name' => 'Guest Author',
        ]);
    }

    /**
     * 获取帖子的作者。
     */
    public function user()
    {
        return $this->belongsTo('App\User')->withDefault(function ($user) {
            $user->name = 'Guest Author';
        });
    }



<a name="updating-many-to-many-relationships"></a>
### 多对多关联

#### 附加 / 分离

Eloquent 也提供了一些额外的辅助方法，使相关模型的使用更加方便。例如，我们假设一个用户可以拥有多个角色，并且每个角色都可以被多个用户共享。给某个用户附加一个角色是通过向中间表插入一条记录实现的，可以使用 `attach` 方法完成该操作：

    $user = App\User::find(1);

    $user->roles()->attach($roleId);

在将关系附加到模型时，还可以传递一组要插入到中间表中的附加数据：

    $user->roles()->attach($roleId, ['expires' => $expires]);

当然，有时也需要移除用户的角色。可以使用 `detach` 移除多对多关联记录。`detach` 方法将会移除中间表对应的记录；但是这 2 个模型都将会保留在数据库中：

    //  移除用户的一个角色...
    $user->roles()->detach($roleId);

    //  移除用户的所有角色...
    $user->roles()->detach();

为了方便，`attach` 和 `detach` 也允许传递一个 ID 数组：

    $user = App\User::find(1);

    $user->roles()->detach([1, 2, 3]);

    $user->roles()->attach([
        1 => ['expires' => $expires],
        2 => ['expires' => $expires]
    ]);

#### 同步关联

你也可以使用 `sync` 方法构建多对多关联。`sync` 方法接收一个 ID 数组以替换中间表的记录。中间表记录中，所有未在 ID 数组中的记录都将会被移除。所以该操作结束后，只有给出数组的 ID 会被保留在中间表中：

    $user->roles()->sync([1, 2, 3]);

你也可以通过 ID 传递额外的附加数据到中间表：

    $user->roles()->sync([1 => ['expires' => true], 2, 3]);

如果你不想移除现有的 ID，可以使用 `syncWithoutDetaching` 方法：



    $user->roles()->syncWithoutDetaching([1, 2, 3]);

#### 切换关联

多对多关联也提供了 `toggle` 方法用于「切换」给定 ID 数组的附加状态。 如果给定的 ID 已被附加在中间表中，那么它将会被移除，同样，如果给定的 ID 已被移除，它将会被附加：

    $user->roles()->toggle([1, 2, 3]);

#### 在中间表上保存额外的数据

当处理多对多关联时，save 方法接收一个额外的数据数组作为第二个参数：

    App\User::find(1)->roles()->save($role, ['expires' => $expires]);

#### 更新中间表记录

如果你需要在中间表中更新一条已存在的记录，可以使用 `updateExistingPivot` 。此方法接收中间表的外键与要更新的数据数组进行更新：

    $user = App\User::find(1);

    $user->roles()->updateExistingPivot($roleId, $attributes);

<a name="touching-parent-timestamps"></a>
## 更新父级时间戳

当一个模型属 `belongsTo` 或者 `belongsToMany` 另一个模型时， 例如 `Comment` 属于 `Post`，有时更新子模型导致更新父模型时间戳非常有用。例如，当 `Comment` 模型被更新时，您要自动「触发」父级 `Post` 模型的 `updated_at` 时间戳的更新。Eloquent 让它变得简单。只要在子模型加一个包含关联名称的 `touches` 属性即可：

    <?php

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Comment extends Model
    {
        /**
         * 要触发的所有关联关系
         *
         * @var array
         */
        protected $touches = ['post'];

        /**
         * 评论所属文章
         */
        public function post()
        {
            return $this->belongsTo('App\Post');
        }
    }

现在，当你更新一个 `Comment` 时，对应父级 `Post` 模型的 `updated_at` 字段也会被同时更新，使其更方便得知何时让一个 `Post` 模型的缓存失效：

    $comment = App\Comment::find(1);

    $comment->text = 'Edit to this comment!';

    $comment->save();
