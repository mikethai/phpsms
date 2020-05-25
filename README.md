# PhpSms
[![StyleCI](https://styleci.io/repos/44543599/shield)](https://styleci.io/repos/44543599)
[![Build Status](https://travis-ci.org/mikecai/phpsms.svg?branch=master)](https://travis-ci.org/mikecai/phpsms)
[![Code Coverage](https://scrutinizer-ci.com/g/mikecai/phpsms/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mikecai/phpsms/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/mikecai/phpsms.svg)](https://packagist.org/packages/mikecai/phpsms)
[![Total Downloads](https://img.shields.io/packagist/dt/mikecai/phpsms.svg)](https://packagist.org/packages/mikecai/phpsms)

可能是目前最聰明，優雅的PHP簡訊發送庫了。

> phpsms的任務均衡調度功能由[toplan/task-balancer](https://github.com/toplan/task-balancer)提供。

特別感謝以下贊助者：

[![簡訊宝](http://toplan.github.io/img/smsbao-logo.png)](http://www.smsbao.com/)

# 特色
- 支持內容簡訊
- 模版簡訊，語音驗證碼，內容語音，模版語音，語音文件。
- 支持發送均衡調度，可按代理器權重值均衡選擇服務商發送。
- 支持一個或多個備用代理器（服務商）。
- 支持代理器計劃方案熱更新，可隨時更新/刪除/新加代理器。
- 允許推入變量，並自定義本質上實現邏輯（與所屬系統鬆散替換）。
- 靈活的發送前後鉤子。
- 內置國內主流服務商的代理器。
- [自定義代理器]（＃自定義代理器）和[寄生代理器]（＃寄生代理器）。

# 服务商

| 服务商 | 模板簡訊 | 内容簡訊 | 语音驗證碼 | 最低消费  |  最低消费单价 | 资费标准
| ----- | :-----: | :-----: | :------: | :-------: | :-----: | :-----:
| [Luosimao](http://luosimao.com)        | × | √ | √ | ￥850(1万条) | ￥0.085/条 
| [Every8D](http://global.every8d.com.tw/) | × | √ | √ | NT 8000(1萬條) | NT0.8/條 
# 安装

```php
composer require mikecai/phpsms:~1.8
```

开发中版本
```php
composer require mikecai/phpsms:dev-master
```

# 快速上手

### 1. 配置

- 配置代理器所需參數

為你需要用到的簡訊服務商(即代理器)配置必要的參數。可以在`config\phpsms.php`中鍵為`agents`的數組中配置，也可以手動在程序中設置，示例如下：

```php
//example:
Sms::config([
    'Luosimao' => [
        'apikey' => 'your api key',
        'voiceApikey' => 'your voice api key',
    ],
    'YunPian'  => [
        'apikey' => 'your api key',
    ],
    'SmsBao' => [
        'username' => 'your username',
        'password'  => 'your password'
    ]
]);
```

- 配置代理器調度方案

可在`config\phpsms.php`中鍵為`scheme`的數組中配置。也可以手動在程序中設置，示例如下：

```php
//example:
Sms::scheme([
    //被使用概率为2/3
    'Luosimao' => '20',

    //被使用概率为1/3，且为备用代理器
    'YunPian' => '10 backup',

    //仅为备用代理器
    'SmsBao' => '0 backup',
]);
```
> **調度方案解析：**
> 如果按照以上配置，那麼系統首次會嘗試使用`Luosimao`或`YunPian`發送簡訊，且它們被使用的概率分別為`2/3`和`1/3`。
> 如果使用其中一個代理器發送失敗，那麼會啟用備用代理器，按照配置可知備用代理器有`YunPian`和`SmsBao`，那麼會依次調用直到發送成功或無備用代理器可用。
> 值得注意的是，如果首次嘗試的是`YunPian`，那麼備用代理器將會只使用`SmsBao`，也就是會排除使用過的代理器。

### 2. Enjoy it!

```php
require('path/to/vendor/autoload.php');
use Toplan\PhpSms\Sms;

// 接收人手机号
$to = '1828****349';
// 簡訊模版
$templates = [
    'YunTongXun' => 'your_temp_id',
    'SubMail'    => 'your_temp_id'
];
// 模版数据
$tempData = [
    'code' => '87392',
    'minutes' => '5'
];
// 簡訊内容
$content = '【签名】这是簡訊内容...';

// 只希望使用模板方式發送簡訊，可以不设置content(如:云通讯、Submail、Ucpaas)
Sms::make()->to($to)->template($templates)->data($tempData)->send();

// 只希望使用内容方式發送，可以不设置模板id和模板data(如:簡訊宝、云片、luosimao)
Sms::make()->to($to)->content($content)->send();

// 同时确保能通过模板和内容方式發送，这样做的好处是可以兼顾到各种类型服务商
Sms::make()->to($to)
    ->template($templates)
    ->data($tempData)
    ->content($content)
    ->send();

// 语音驗證碼
Sms::voice('02343')->to($to)->send();

// 语音驗證碼兼容模版语音(如阿里大鱼的文本转语音)
Sms::voice('02343')
    ->template('Alidayu', 'your_tts_code')
    ->data(['code' => '02343'])
    ->to($to)
    ->send();
```

### 3. 在laravel和lumen中使用

* 服务提供器

```php
//服务提供器
'providers' => [
    ...
    Toplan\PhpSms\PhpSmsServiceProvider::class,
]

//别名
'aliases' => [
    ...
    'PhpSms' => Toplan\PhpSms\Facades\Sms::class,
]
```

* 生成配置文件

```php
php artisan vendor:publish
```
生成的配置文件為config/phpsms.php，然後在該文件中按提示配置。

* 使用

詳見API，示例：
```php
PhpSms::make()->to($to)->content($content)->send();
```

# API

## API - 全局配置

### Sms::scheme([$name[, $scheme]])

設置/獲取代理器的調度方案。

> 調度配置支持熱更新，即在應用系統的整個運行過程中都能隨時修改。

- 設置

手動設置代理器調度方案(優先級高於配置文件)，如：
```php
Sms::scheme([
    'SmsBao' => '80 backup'
    'YunPian' => '100 backup'
]);
//或
Sms::scheme('SmsBao', '80 backup');
Sms::scheme('YunPian', '100 backup');
```
- 获取

通过该方法还能获取所有或指定代理器的调度方案，如：
```php
//获取所有的调度方案:
$scheme = Sms::scheme();

//获取指定代理器的调度方案:
$scheme['SmsBao'] = Sms::scheme('SmsBao');
```

> `scheme`静态方法的更多使用方法见[高级调度配置](#高级调度配置)

### Sms::config([$name[, $config][, $override]]);

设置/获取代理器的配置数据。

> 参数配置支持热更新，即在应用系统的整个运行過程中都能随时修改。

- 设置

手动设置代理器的配置数据(优先级高于配置文件)，如：
```php
Sms::config([
   'SmsBao' => [
       'username' => ...,
       'password' => ...,
   ]
]);
//或
Sms::config('SmsBao', [
   'username' => ...,
   'password' => ...,
]);
```
- 获取

通过该方法还能获取所有或指定代理器的配置参数，如：
```php
//获取所有的配置:
$config = Sms::config();

//获取指定代理器的配置:
$config['SmsBao'] = Sms::config('SmsBao');
```

### Sms::beforeSend($handler[, $override]);

發送前钩子，示例：
```php
Sms::beforeSend(function($task, $index, $handlers, $prevReturn){
    //获取簡訊数据
    $smsData = $task->data;
    ...
    //如果返回false会终止發送任务
    return true;
});
```
> 更多细节请查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `beforeRun` 钩子

### Sms::beforeAgentSend($handler[, $override]);

代理器發送前钩子，示例：
```php
Sms::beforeAgentSend(function($task, $driver, $index, $handlers, $prevReturn){
    //簡訊数据:
    $smsData = $task->data;
    //当前使用的代理器名称:
    $agentName = $driver->name;
    //如果返回false会停止使用当前代理器
    return true;
});
```
> 更多细节请查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `beforeDriverRun` 钩子

### Sms::afterAgentSend($handler[, $override]);

代理器發送后钩子，示例：
```php
Sms::afterAgentSend(function($task, $agentResult, $index, $handlers, $prevReturn){
     //$result为代理器的發送结果数据
     $agentName = $agentResult['driver'];
     ...
});
```
> 更多细节请查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `afterDriverRun` 钩子

### Sms::afterSend($handler[, $override]);

發送后钩子，示例：
```php
Sms::afterSend(function($task, $taskResult, $index, $handlers, $prevReturn){
    //$result为發送后获得的结果数组
    $success = $taskResult['success'];
    ...
});
```
> 更多细节请查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `afterRun` 钩子

### Sms::queue([$enable[, $handler]])

该方法可以设置是否启用队列以及定义如何推送到队列。

`$handler`匿名函数可使用的参数:
+ `$sms` : Sms实例
+ `$data` : Sms实例中的簡訊数据，等同于`$sms->all()`

定义如何推送到队列：
```php
//自动启用队列
Sms::queue(function($sms, $data){
    //define how to push to queue.
    ...
});

//第一个参数为true,启用队列
Sms::queue(true, function($sms, $data){
    //define how to push to queue.
    ...
});

//第一个参数为false,暂时关闭队列
Sms::queue(false, function($sms, $data){
    //define how to push to queue.
    ...
});
```

如果已经定义过如何推送到队列，还可以继续设置关闭/开启队列：
```php
Sms::queue(true);//开启队列
Sms::queue(false);//关闭队列
```

获取队列启用情况：
```php
$enable = Sms::queue();
//为true,表示当前启用了队列。
//为false,表示当前关闭了队列。
```

## API - 發送相关

### Sms::make()

生成發送簡訊的sms实例，并返回实例。
```php
$sms = Sms::make();

//创建实例的同时设置簡訊内容：
$sms = Sms::make('【签名】这是簡訊内容...');

//创建实例的同时设置簡訊模版：
$sms = Sms::make('YunTongXun', 'your_temp_id');
//或
$sms = Sms::make([
    'YunTongXun' => 'your_temp_id',
    'SubMail' => 'your_temp_id',
    ...
]);
```

### Sms::voice()

生成發送语音驗證碼的sms实例，并返回实例。
```php
$sms = Sms::voice();

//创建实例的同时设置驗證碼
$sms = Sms::voice($code);
```

> - 如果你使用`Luosimao`语音驗證碼，还需用在配置文件中`Luosimao`选项中设置`voiceApikey`。
> - **语音文件ID**即是在服务商配置的语音文件的唯一编号，比如阿里大鱼[语音通知](http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.oORhh9&apiId=25445)的`voice_code`。
> - **模版语音**是另一种语音请求方式，它是通过模版ID和模版数据进行的语音请求，比如阿里大鱼的[文本转语音通知](http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.f04PJ3&apiId=25444)。

### type($type)

设置实例类型，可选值有`Sms::TYPE_SMS`和`Sms::TYPE_VOICE`，返回实例对象。

### to($mobile)

设置發送给谁，并返回实例。
```php
$sms->to('1828*******');

//兼容腾讯云
$sms->to([86, '1828*******'])
```

### template($agentName, $id)

指定代理器设置模版或批量设置，并返回实例。
```php
//设置指定服务商的模板id
$sms->template('YunTongXun', 'your_temp_id')
    ->template('SubMail', 'your_temp_id');

//一次性设置多个服务商的模板id
$sms->template([
    'YunTongXun' => 'your_temp_id',
    'SubMail' => 'your_temp_id',
    ...
]);
```

### data($key, $value)

设置模板簡訊的模板数据，并返回实例对象。
```php
//单个数据
$sms->data('code', $code);

//同时设置多个数据
$sms->data([
    'code' => $code,
    'minutes' => $minutes
]);
```

> 通过`template`和`data`方法的组合除了可以实现模版簡訊的数据填充，还可以实现模版语音的数据填充。

### content($text)

设置内容簡訊的内容，并返回实例对象。

> 一些内置的代理器(如SmsBao、YunPian、Luosimao)使用的是内容簡訊(即直接發送簡訊内容)，那么就需要为它们设置簡訊内容。

```php
$sms->content('【签名】这是簡訊内容...');
```

### code($code)

设置语音驗證碼，并返回实例对象。

### file($agentName, $id)

设置语音文件，并返回实例对象。
```php
$sms->file('Agent1', 'agent1_file_id')
    ->file('Agent2', 'agent2_file_id');

//或
$sms->file([
    'Agent1' => 'agent1_file_id',
    'Agent2' => 'agent2_fiile_id',
]);
```

### params($agentName, $params)

直接设置参数到服务商提供的原生接口上，并返回实例对象。
```php
$sms->params('Agent1', [
    'callbackUrl' => ...,
    'userData'    => ...,
]);

//或
$sms->params([
    'Agent1' => [
        'callbackUrl' => ...,
        'userData'    => ...,
    ],
    'Agent2' => [
        ...
    ],
]);
```

### all([$key])

获取Sms实例中的簡訊数据，不带参数时返回所有数据，其结构如下：
```php
[
    'type'      => ...,
    'to'        => ...,
    'templates' => [...],
    'data'      => [...], // template data
    'content'   => ...,
    'code'      => ...,   // voice code
    'files'     => [...], // voice files
    'params'    => [...],
]
```

### agent($name)

临时设置發送时使用的代理器(不会影响备用代理器的正常使用)，并返回实例，`$name`为代理器名称。
```php
$sms->agent('SmsBao');
```
> 通过该方法设置的代理器将获得绝对优先权，但只对当前簡訊实例有效。

### send()

请求發送簡訊/语音驗證碼。
```php
//会遵循是否使用队列
$result = $sms->send();

//忽略是否使用队列
$result = $sms->send(true);
```

> `$result`数据结构请参看[task-balancer](https://github.com/toplan/task-balancer)

# 自定义代理器

- step 1

可将配置项(如果有用到)加入到`config/phpsms.php`中键为`agents`的数组里。

```php
//example:
'Foo' => [
    'key' => 'your api key',
    ...
]
```

- step 2

新建一个继承`Toplan\PhpSms\Agent`抽象类的代理器类，建议代理器类名为`FooAgent`，建议命名空间为`Toplan\PhpSms`。

> 如果类名不为`FooAgent`或者命名空间不为`Toplan\PhpSms`，在使用该代理器时则需要指定代理器类，详见[高级调度配置](#高级调度配置)。

- step 3

实现相应的接口，可选的接口有:

| 接口           | 说明         |
| ------------- | :----------: |
| ContentSms    | 發送内容簡訊   |
| TemplateSms   | 發送模版簡訊   |
| VoiceCode     | 發送语音驗證碼 |
| ContentVoice  | 發送内容语音   |
| TemplateVoice | 發送模版语音   |
| FileVoice     | 發送文件语音   |

# 高级调度配置

代理器的高级调度配置可以通过配置文件(`config/phpsms.php`)中的`scheme`项目配置，也可以通过`scheme`静态方法设置。
值得注意的是，高级调度配置的值的数据结构是数组。

### 指定代理器类

如果你自定义了一个代理器，类名不为`FooAgent`或者命名空间不为`Toplan\PhpSms`，
那么你还可以在调度配置时指定你的代理器使用的类。

* 配置方式：

通过配置值中`agentClass`键来指定类名。

* 示例：
```php
Sms::scheme('agentName', [
    '10 backup',
    'agentClass' => 'My\Namespace\MyAgentClass'
]);
```

### 寄生代理器

如果你既不想使用內置的代理器，也不想創建文件寫自定義代理器，那麼寄生代理器或許是個好的選擇，
無需定義代理器類，只需在調度配置時定義好發送簡訊和語音驗證碼的方式即可。

* 配置方式：

可以配置的發送過程有:

| 發送過程           | 参数列表                        | 说明         |
| ----------------- | :---------------------------: | :----------: |
| sendContentSms    | $agent, $to, $content         | 發送内容簡訊   |
| sendTemplateSms   | $agent, $to, $tmpId, $tmpData | 發送模版簡訊   |
| sendVoiceCode     | $agent, $to, $code            | 發送语音驗證碼  |
| sendContentVoice  | $agent, $to, $content         | 發送内容语音   |
| sendTemplateVoice | $agent, $to, $tmpId, $tmpData | 發送模版语音   |
| sendFileVoice     | $agent, $to, $fileId          | 發送文件语音   |

* 示例：
```php
Sms::scheme([
    'agentName' => [
        '20 backup',
        'sendContentSms' => function($agent, $to, $content){
            // 獲取配置(如果設置了的話):
            $key = $agent->key;
            ...
            // 可使用的内置方法:
            $agent->curlGet($url, $params); //get
            $agent->curlPost($url, $params); //post
            ...
            // 更新發送結果:
            $agent->result(Agent::SUCCESS, true);
            $agent->result(Agent::INFO, 'some info');
            $agent->result(Agent::CODE, 'your code');
        },
        'sendVoiceCode' => function($agent, $to, $code){
            // 發送語音驗證碼，同上
        }
    ]
]);
```

# Todo

- [ ] 重新實現云通訊代理器，去掉`lib/CCPRestSmsSDK.php`
- [ ] 重新實現雲之訊代理器，去掉`lib/Ucpaas.php`
- [ ] 升級雲片接口到v2版本

# License

MIT
