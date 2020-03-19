# Markdown 编写排版

对于技术人来讲，技术文章就是我们的门面。想了解一个技术人的能力，或者潜力，可以从他的博客文章中开始。撇去内容不讲，扫一眼排版是否干净清爽，直接就可以告诉你关于作者的很多信息：做事是否用心、思维是否清晰、对自己是否有高要求。

社区里许多内推岗位，都是由技术负责人或者 CTO 亲自跟进，技术博客作为简历的一部分，很多时候都会是他们对应聘者的第一印象。排版混乱的博客，给到招聘方就是一副邋遢不堪的形象，这个时候技术博客是减分的。

另外社区里充斥着排版杂乱的内容，也会影响整个社区的品质，让人感觉不专业，在这里混的人也会觉得没有面子。正如某个伟人所说的：

> 作为技术人，连个文章排版都整不好，何以码代码呢？

## 一、空格

> 有研究显示，打字的时候不喜欢在中文和英文之间加空格的人，感情路都走得很辛苦，有七成的比例会在 34 岁的时候跟自己不爱的人结婚，而其余三成的人最后只能把遗产留给自己的猫。 毕竟爱情跟书写都需要适时地留白。与大家共勉之。
> 
> ------[vinta/paranoid-auto-spacing](https://github.com/vinta/pangu.js)

### 1. 中英文之间需要增加空格

正确：

```
Laravel 是一套简洁、优雅的 PHP Web 开发框架。
```

错误：

```
Laravel是一套简洁、优雅的PHP Web开发框架。
```

```
Laravel 是一套简洁、优雅的PHP Web 开发框架。
```

完整的正确例子：

> Laravel 天生就具有测试的基因。事实上，Laravel 默认就支持用 PHPUnit 来做测试，并为你的应用程序配置好了 `phpunit.xml` 文件。框架还提供了一些便利的辅助函数，让你可以更直观的测试你的应用程序。默认情况，你的应用 `tests` 目录中将会包含两个子目录： `Feature` 和 `Unit`。

例外：「豆瓣FM」等产品名词，按照官方所定义的格式书写。

### 2. 中文与数字之间需要增加空格

正确：

> 今天出去买菜花了 5000 元。

错误：

```
今天出去买菜花了5000元。
```

```
今天出去买菜花了 5000元。
```

### 3. 数字与单位之间需要增加空格

正确：

> 我家的光纤入屋宽频有 10 Gbps，SSD 一共有 20 TB

错误：

```
我家的光纤入屋宽频有 10Gbps，SSD 一共有 20TB
```

例外：度 / 百分比与数字之间不需要增加空格：

正确：

> 今天是 233° 的高温。
>
> 新 MacBook Pro 有 15% 的 CPU 性能提升。

错误：

```
今天是 233 ° 的高温。
```

```
新 MacBook Pro 有 15 % 的 CPU 性能提升。
```

### 4. 全角标点与其他字符之间不加空格

正确：

> 刚刚买了一部 iPhone，好开心！

错误：

```
刚刚买了一部 iPhone ，好开心！
```

```
刚刚买了一部 iPhone， 好开心！
```

## 二、标点符号

### 不重复使用标点符号

正确：

> 德国队竟然战胜了巴西队！
>
> 她竟然对你说「喵」？！

错误：

> 德国队竟然战胜了巴西队！！
>
> 德国队竟然战胜了巴西队！！！！！！！！
>
> 她竟然对你说「喵」？？！！
>
> 她竟然对你说「喵」？！？！？？！！

## 三、全角和半角

不明白什么是全角（全形）与半角（半形）符号？请阅读以下链接：

- [百度知道：输入法里面,全角和半角有什么区别啊?](https://zhidao.baidu.com/question/2088836)
- [知乎：中文输入法为什么会有全角和半角的区别？](https://www.zhihu.com/question/19605819)

### 1. 使用全角中文标点

正确：

> 嗨！你知道嘛？今天前台的小妹跟我说「喵」了哎！
>
> 核磁共振成像（NMRI）是什么原理都不知道？JFGI！

错误：

```
嗨! 你知道嘛? 今天前台的小妹跟我说 "喵" 了哎！
```

```
嗨!你知道嘛?今天前台的小妹跟我说"喵"了哎！
```

```
核磁共振成像 (NMRI) 是什么原理都不知道? JFGI!
```

```
核磁共振成像(NMRI)是什么原理都不知道?JFGI!
```

### 2. 数字使用半角字符

正确：

> 这件蛋糕只卖 1000 元。

错误：

```
这件蛋糕只卖 １０００ 元。
```

例外：在设计稿、宣传海报中如出现极少量数字的情形时，为方便文字对齐，是可以使用全形数字的。

### 3. 遇到完整的英文整句、特殊名词，其内容使用半角标点

正确：

> 贾伯斯那句话是怎么说的？「Stay hungry, stay foolish.」
>
> 推荐你阅读《Hackers & Painters: Big Ideas from the Computer Age》，非常的有趣。

错误：

```
贾伯斯那句话是怎么说的？「Stay hungry，stay foolish。」
```

```
推荐你阅读《Hackers＆Painters：Big Ideas from the Computer Age》，非常的有趣。
```

## 四、名词

### 1. 专有名词使用正确的大小写

大小写相关用法原属于英文书写范畴，不属于本文讨论内容，在这里只对部分易错用法进行简述。

正确：

> 使用 GitHub 登录
>
> 我们的客户有 GitHub、Foursquare、Microsoft Corporation、Google、Facebook, Inc.。

错误：

> 使用 github 登录
>
> 使用 GITHUB 登录
>
> 使用 Github 登录
>
> 使用 gitHub 登录
>
> 使用 gｲんĤЦ8 登录
>
> 我们的客户有 github、foursquare、microsoft corporation、google、facebook, inc.。
>
> 我们的客户有 GITHUB、FOURSQUARE、MICROSOFT CORPORATION、GOOGLE、FACEBOOK, INC.。
>
> 我们的客户有 Github、FourSquare、MicroSoft Corporation、Google、FaceBook, Inc.。
>
> 我们的客户有 gitHub、fourSquare、microSoft Corporation、google、faceBook, Inc.。
>
> 我们的客户有 gｲんĤЦ8、ｷouЯƧquﾑгє、๓เςг๏ร๏Ŧt ς๏гק๏гคtเ๏ภn、900913、ƒ4ᄃëв๏๏к, IПᄃ.。

### 2. 不要使用不地道的缩写

正确：

> 我们需要一位熟悉 JavaScript、HTML5，至少理解一种框架（如 Backbone.js、AngularJS、React 等）的前端开发者。

错误：

> 我们需要一位熟悉 Js、h5，至少理解一种框架（如 backbone、angular、RJS 等）的 FED。

## 五、杂项

以下用法略带有个人色彩，即：无论是否遵循下述规则，从语法的角度来讲都是正确的。

### 1. 链接之间增加空格

用法：

> 请 [提交一个 issue](https://github.com/sparanoid/chinese-copywriting-guidelines/blob/master/README.zh-CN.md#) 并分配给相关同事。
>
> 访问我们网站的最新动态，请 [点击这里](https://github.com/sparanoid/chinese-copywriting-guidelines/blob/master/README.zh-CN.md#) 进行订阅！

对比用法：

> 请[提交一个 issue](https://github.com/sparanoid/chinese-copywriting-guidelines/blob/master/README.zh-CN.md#)并分配给相关同事。
>
> 访问我们网站的最新动态，请[点击这里](https://github.com/sparanoid/chinese-copywriting-guidelines/blob/master/README.zh-CN.md#)进行订阅！

### 2. 简体中文使用直角引号

用法：

> 「老师，『有条不紊』的『紊』是什么意思？」

对比用法：

```
"老师，'有条不紊'的'紊'是什么意思？"
```

## 附录：工具

| 仓库 | 语言 |
| --- | --- |
| [vinta/paranoid-auto-spacing](https://github.com/vinta/paranoid-auto-spacing) | JavaScript |
| [huei90/pangu.node](https://github.com/huei90/pangu.node) | Node.js |
| [huacnlee/auto-correct](https://github.com/huacnlee/auto-correct) | Ruby |
| [sparanoid/space-lover](https://github.com/sparanoid/space-lover) | PHP (WordPress) |
| [nauxliu/auto-correct](https://github.com/NauxLiu/auto-correct) | PHP |
| [jxlwqq/chinese-typesetting](https://github.com/jxlwqq/chinese-typesetting) | PHP |
| [hotoo/pangu.vim](https://github.com/hotoo/pangu.vim) | Vim |
| [sparanoid/grunt-auto-spacing](https://github.com/sparanoid/grunt-auto-spacing) | Node.js (Grunt) |
| [hjiang/scripts/add-space-between-latin-and-cjk](https://github.com/hjiang/scripts/blob/master/add-space-between-latin-and-cjk) | Python |
| [hustcc/hint](https://github.com/hustcc/hint) | Python |

## 附录：谁在这样做？

| 网站 | 文案 | UGC |
| --- | --- | --- |
| [Apple 中国](http://www.apple.com/cn/) | 是 | N/A |
| [Apple 香港](http://www.apple.com/hk/) | 是 | N/A |
| [Apple 台湾](http://www.apple.com/tw/) | 是 | N/A |
| [Microsoft 中国](http://www.microsoft.com/zh-cn/) | 是 | N/A |
| [Microsoft 香港](http://www.microsoft.com/zh-hk/) | 是 | N/A |
| [Microsoft 台湾](http://www.microsoft.com/zh-tw/) | 是 | N/A |
| [LeanCloud](https://leancloud.cn/) | 是 | N/A |
| [V2EX](https://www.v2ex.com/) | 是 | 是 |
| [Apple4us](http://apple4us.com/) | 是 | N/A |
| [Ruby China](https://ruby-china.org/) | 是 | 标题达成 |
| [LearnKu](https://learnku.com/) | 是 | 标题达成 |
| [少数派](http://sspai.com/) | 是 | N/A |
 
## 附录：参考

- [中文文案排版指北](https://github.com/sparanoid/chinese-copywriting-guidelines/blob/master/README.zh-CN.md) 

