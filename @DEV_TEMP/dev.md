#### ORM 操作
* 数据查询
	- 查询单一数据,返回单条数据对象
		* 创建model 对象
		> $customer = Customer::findOne(['id' => $id]);
		* 临时model对象
		> $model3 = new  Model('{{%member}}');
		$article = $model3->findOne(['uid' => 3]);
		* 作为数组返回
		> $array = Customer::findOne(['id' => $id])->asArray();
	* 查询多条数据集合,返回多条数据对象的数组集合
		* 创建model 对象
		> $customer = Customer::findAll(['id' => $id]);
		* 临时model对象
		> $model3 = new  Model('{{%member}}');
		$article = $model3->findAll(['uid' => 3]);
		* 作为数组集合返回
			* 全部强制数组返回
			> $arrays = Customer::findAll(['id' => $id],FF::DB_FINDALL_ASARRAY);
			* 单一元素对象转换为数组
			> $customer = Customer::findAll(['id' => $id]);
			$arrays = $customer[0]->asArray();
		
*  数据插入
> $customer = new Customer;
$customer->name = $name;
$customer->email = $email;
$customer->insert();

* 数据更新
>$customer = Customer::findOne(['id' => $id]);
$customer->name = $name;
$customer->email = $email;
$customer->update();
#### DB Command操作

