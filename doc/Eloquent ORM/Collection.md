# Eloquent: 集合

- [简介](#introduction)
- [可用的方法](#available-methods)
- [自定义集合](#custom-collections)

<a name="introduction"></a>
## 简介

Eloquent 返回的所有多结果集都是 `Illuminate\Database\Eloquent\Collection` 对象的实例，包括通过 `get` 方法检索或通过访问关联关系获取到的结果。 Eloquent 的集合对象继承了 Laravel 的 [集合基类](/docs/{{version}}/collections) ，因此它自然也继承了数十种能优雅地处理 Eloquent 模型底层数组的方法。

当然，所有的集合都可以作为迭代器，你可以像遍历简单的 PHP 数组一样来遍历它们：

    $users = App\User::where('active', 1)->get();

    foreach ($users as $user) {
        echo $user->name;
    }

不过，集合比数组更加强大，它通过更直观的接口暴露出可链式调用的 map/reduce 等操作。例如，让我们移除所有未激活的用户并收集剩余用户的名字：

    $users = App\User::all();

    $names = $users->reject(function ($user) {
        return $user->active === false;
    })
    ->map(function ($user) {
        return $user->name;
    });

> {note} 大多数 Eloquent 集合方法会返回新的 Eloquent 集合实例，但是 `pluck` ， `keys` ， `zip` ， `collapse` ， `flatten` 和 `flip` 方法除外，它们会返回一个 [集合基类](/docs/{{version}}/collections) 实例。同样，结果 `map` 操作返回的集合不包括任何 Eloquent 模型，那么它会被自动转换成集合基类。

<a name="available-methods"></a>
## 可用的方法

### 集合基类

所有 Eloquent 都继承了基础的 [Laravel 集合](https://learnku.com/docs/laravel/5.7/collections) 对象；因此，它们也继承了所有集合基类提供的强大的方法：

[all](https://learnku.com/docs/laravel/5.7/collections#method-all)
[average](https://learnku.com/docs/laravel/5.7/collections#method-average)
[avg](https://learnku.com/docs/laravel/5.7/collections#method-avg)
[chunk](https://learnku.com/docs/laravel/5.7/collections#method-chunk)
[collapse](https://learnku.com/docs/laravel/5.7/collections#method-collapse)
[combine](https://learnku.com/docs/laravel/5.7/collections#method-combine)
[concat](https://learnku.com/docs/laravel/5.7/collections#method-concat)
[contains](https://learnku.com/docs/laravel/5.7/collections#method-contains)
[containsStrict](https://learnku.com/docs/laravel/5.7/collections#method-containsstrict)
[count](https://learnku.com/docs/laravel/5.7/collections#method-count)
[crossJoin](https://learnku.com/docs/laravel/5.7/collections#method-crossjoin)
[dd](https://learnku.com/docs/laravel/5.7/collections#method-dd)
[diff](https://learnku.com/docs/laravel/5.7/collections#method-diff)
[diffKeys](https://learnku.com/docs/laravel/5.7/collections#method-diffkeys)
[dump](https://learnku.com/docs/laravel/5.7/collections#method-dump)
[each](https://learnku.com/docs/laravel/5.7/collections#method-each)
[eachSpread](https://learnku.com/docs/laravel/5.7/collections#method-eachspread)
[every](https://learnku.com/docs/laravel/5.7/collections#method-every)
[except](https://learnku.com/docs/laravel/5.7/collections#method-except)
[filter](https://learnku.com/docs/laravel/5.7/collections#method-filter)
[first](https://learnku.com/docs/laravel/5.7/collections#method-first)
[flatMap](https://learnku.com/docs/laravel/5.7/collections#method-flatmap)
[flatten](https://learnku.com/docs/laravel/5.7/collections#method-flatten)
[flip](https://learnku.com/docs/laravel/5.7/collections#method-flip)
[forget](https://learnku.com/docs/laravel/5.7/collections#method-forget)
[forPage](https://learnku.com/docs/laravel/5.7/collections#method-forpage)
[get](https://learnku.com/docs/laravel/5.7/collections#method-get)
[groupBy](https://learnku.com/docs/laravel/5.7/collections#method-groupby)
[has](https://learnku.com/docs/laravel/5.7/collections#method-has)
[implode](https://learnku.com/docs/laravel/5.7/collections#method-implode)
[intersect](https://learnku.com/docs/laravel/5.7/collections#method-intersect)
[isEmpty](https://learnku.com/docs/laravel/5.7/collections#method-isempty)
[isNotEmpty](https://learnku.com/docs/laravel/5.7/collections#method-isnotempty)
[keyBy](https://learnku.com/docs/laravel/5.7/collections#method-keyby)
[keys](https://learnku.com/docs/laravel/5.7/collections#method-keys)
[last](https://learnku.com/docs/laravel/5.7/collections#method-last)
[map](https://learnku.com/docs/laravel/5.7/collections#method-map)
[mapInto](https://learnku.com/docs/laravel/5.7/collections#method-mapinto)
[mapSpread](https://learnku.com/docs/laravel/5.7/collections#method-mapspread)
[mapToGroups](https://learnku.com/docs/laravel/5.7/collections#method-maptogroups)
[mapWithKeys](https://learnku.com/docs/laravel/5.7/collections#method-mapwithkeys)
[max](https://learnku.com/docs/laravel/5.7/collections#method-max)
[median](https://learnku.com/docs/laravel/5.7/collections#method-median)
[merge](https://learnku.com/docs/laravel/5.7/collections#method-merge)
[min](https://learnku.com/docs/laravel/5.7/collections#method-min)
[mode](https://learnku.com/docs/laravel/5.7/collections#method-mode)
[nth](https://learnku.com/docs/laravel/5.7/collections#method-nth)
[only](https://learnku.com/docs/laravel/5.7/collections#method-only)
[pad](https://learnku.com/docs/laravel/5.7/collections#method-pad)
[partition](https://learnku.com/docs/laravel/5.7/collections#method-partition)
[pipe](https://learnku.com/docs/laravel/5.7/collections#method-pipe)
[pluck](https://learnku.com/docs/laravel/5.7/collections#method-pluck)
[pop](https://learnku.com/docs/laravel/5.7/collections#method-pop)
[prepend](https://learnku.com/docs/laravel/5.7/collections#method-prepend)
[pull](https://learnku.com/docs/laravel/5.7/collections#method-pull)
[push](https://learnku.com/docs/laravel/5.7/collections#method-push)
[put](https://learnku.com/docs/laravel/5.7/collections#method-put)
[random](https://learnku.com/docs/laravel/5.7/collections#method-random)
[reduce](https://learnku.com/docs/laravel/5.7/collections#method-reduce)
[reject](https://learnku.com/docs/laravel/5.7/collections#method-reject)
[reverse](https://learnku.com/docs/laravel/5.7/collections#method-reverse)
[search](https://learnku.com/docs/laravel/5.7/collections#method-search)
[shift](https://learnku.com/docs/laravel/5.7/collections#method-shift)
[shuffle](https://learnku.com/docs/laravel/5.7/collections#method-shuffle)
[slice](https://learnku.com/docs/laravel/5.7/collections#method-slice)
[sort](https://learnku.com/docs/laravel/5.7/collections#method-sort)
[sortBy](https://learnku.com/docs/laravel/5.7/collections#method-sortby)
[sortByDesc](https://learnku.com/docs/laravel/5.7/collections#method-sortbydesc)
[splice](https://learnku.com/docs/laravel/5.7/collections#method-splice)
[split](https://learnku.com/docs/laravel/5.7/collections#method-split)
[sum](https://learnku.com/docs/laravel/5.7/collections#method-sum)
[take](https://learnku.com/docs/laravel/5.7/collections#method-take)
[tap](https://learnku.com/docs/laravel/5.7/collections#method-tap)
[toArray](https://learnku.com/docs/laravel/5.7/collections#method-toarray)
[toJson](https://learnku.com/docs/laravel/5.7/collections#method-tojson)
[transform](https://learnku.com/docs/laravel/5.7/collections#method-transform)
[union](https://learnku.com/docs/laravel/5.7/collections#method-union)
[unique](https://learnku.com/docs/laravel/5.7/collections#method-unique)
[uniqueStrict](https://learnku.com/docs/laravel/5.7/collections#method-uniquestrict)
[unless](https://learnku.com/docs/laravel/5.7/collections#method-unless)
[values](https://learnku.com/docs/laravel/5.7/collections#method-values)
[when](https://learnku.com/docs/laravel/5.7/collections#method-when)
[where](https://learnku.com/docs/laravel/5.7/collections#method-where)
[whereStrict](https://learnku.com/docs/laravel/5.7/collections#method-wherestrict)
[whereIn](https://learnku.com/docs/laravel/5.7/collections#method-wherein)
[whereInStrict](https://learnku.com/docs/laravel/5.7/collections#method-whereinstrict)
[whereNotIn](https://learnku.com/docs/laravel/5.7/collections#method-wherenotin)
[whereNotInStrict](https://learnku.com/docs/laravel/5.7/collections#method-wherenotinstrict)
[zip](https://learnku.com/docs/laravel/5.7/collections#method-zip)



<a name="自定义集合"></a>
## 自定义集合

如果你需要在自己的扩展方法中使用自定义的 `Collection` 对象，你可以在你的模型中重写 `newCollection`方法：

    <?php

    namespace App;

    use App\CustomCollection;
    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
         * 创建一个新的 Eloquent 集合实例对象。
         *
         * @param  array  $models
         * @return \Illuminate\Database\Eloquent\Collection
         */
        public function newCollection(array $models = [])
        {
            return new CustomCollection($models);
        }
    }

一旦你定义了 `newCollection` 方法，任何时候都可以在 Eloquent 返回的模型的 `Collection` 实例中获得你的自定义集合实例。如果你想要在应用程序的每个模型中使用同一个自定义集合，则应该在所有的模型继承的模型基类中重写 `newCollection` 方法。
