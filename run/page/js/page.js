var URL = window.location.protocol+"//complaint.lwhs.me/",
	PATH = "?doman=" + window.location.origin;
function get_params(name) {
	var reg = new RegExp(/[0-9a-zA-Z]+/);
	var r = window.location.pathname.match(reg);

	if(r != null) {
		return r[0];
	} else {
		console.error("URL上的没有websiteName参数！");
		return;
	}
}
Vue.component("checkbox-groups", { /*偏大或者偏小多选控制组件*/
	props: ['type0', 'catid'],
	template: '<el-checkbox-group v-model="checkList" @change="changeSel">' +
		'	<el-checkbox  v-for="item in namelist" :label="item.val" v-hide="isshow(item.val)" >' +
		'{{item.name}}</el-checkbox>' +
		'</el-checkbox-group>',
	data: function() {
		return {
			namelist: [], //选项list
			checkList: [], //数据
		}
	},
	methods: {
		isshow: function(ids) {
			var that = this;
			var cat_id = Number(that.catid) || 0,
				swt = false;
			return swt;
			/*以下搁置不用*/
			switch(cat_id) {
				case 161: //连衣裙
					if(ids == 0 || ids == 1) { //不展示的id
						swt = true;
					}
					break;
				case 297: //连体裤
					if(ids == 0 || ids == 1) {
						swt = true;
					}
					break;
				case 2: //上衣
					if(ids == 0 || ids == 1) {
						swt = true;
					}
					break;
				case 2: //牛仔
					if(ids == 0 || ids == 1) {
						swt = true;
					}
					break;
				case 2: //泳衣
					if(ids == 0 || ids == 1) {
						swt = true;
					}
					break;
				default:
					swt = false;
			}
			return swt;
		},
		changeSel: function(list0) {
			var that = this;
			that.$emit("listenmsg", {
				types: that.type0,
				list: list0
			})
		},
	},
	created: function() {

	},
	mounted: function() {
		var that = this;
		switch(that.type0) {
			case "134":
				that.namelist = [{
					name: "Bust too small", //胸部偏小
					val: "140"
				}, {
					name: "Waist too small", //腰部偏小
					val: "141"
				}, {
					name: "Hip too small", //臀部偏小
					val: "142"
				}, {
					name: "Neck too small", //颈部偏小
					val: "143"
				}, {
					name: "Arm too small", //臂部偏小
					val: "144"
				}, {
					name: "Thigh too small", //腿部偏小
					val: "145"
				}]
				break;
			case "135":
				that.namelist = [{
					name: "Bust too large", //胸部偏大
					val: "146"
				}, {
					name: "Waist too large", //腰部偏大
					val: "147"
				}, {
					name: "Hip too large", //臀部偏大
					val: "148"
				}, {
					name: "Neck too large", //颈部偏大
					val: "149"
				}, {
					name: "Arm too large", //臂部偏大
					val: "150"
				}, {
					name: "Thigh too large", //腿部偏大
					val: "151"
				}]
				break;
		}

	},
	watch: {

	},
});

Vue.component("return-reasons", { //第三页的分支-----返回原因组件
	template: '<div>' +
		'				<div class="reasonBox" v-for="(x,idx1) in reasonList1" :key="x.id">' +
		'					<div class="reasonItem" v-for="(y,idx2) in x.children" :key="y.id">' +
		'						<p class="reasonTitle font0">{{y.englishName}}</p>' +
		'						<div class="lines">' +
		'							<el-checkbox-group v-model="modelList[(idx1==0?(idx1+idx2):(idx1==1?5:6))]" v-if="!((idx1+idx2)==3&&idx1==0)" @change="getChangeData">' +
		'								<div v-for="n in Math.ceil(y.children.length/2)" :key="n">' +
		'									<div class="cellbox">' +
		'										<div class="cell" v-for="z in y.children.slice(2*(n-1),2*n)" :key="z.id">' +
		'											<el-checkbox :label="z.id">{{z.englishName}}</el-checkbox>' +
		'										</div>' +
		'									</div>' +
		'								</div>' +
		'							</el-checkbox-group>' +
		'							<el-radio-group v-model="modelList[(idx1==0?(idx1+idx2):(idx1==1?5:6))]" v-if="((idx1+idx2)==3&&idx1==0)" @change="getChangeData">' +
		'								<div v-for="n in Math.ceil(y.children.length/2)" :key="n">' +
		'									<div class="cellbox">' +
		'										<div class="cell" v-for="z in y.children.slice(2*(n-1),2*n)" :key="z.id">' +
		'											<i class="el-icon-arrow-right icon" v-show="showselsboxId!=z.id" v-if="z.id==\'134\'||z.id==\'135\'" ></i>' +
		'											<i class="el-icon-arrow-down icon" v-show="showselsboxId==z.id" :class="{active:showselsboxId==z.id}" v-if="z.id==\'134\'||z.id==\'135\'"></i>' +
		'											<el-radio :label="z.id">{{z.englishName}}</el-radio>' +
		'										</div>' +
		'									</div>' +
		'									<div v-for="k in y.children.slice(2*(n-1),2*n)" :key="k.id" class="selsomebox" v-if="(k.id==\'134\'||k.id==\'135\')&&showselsboxId==k.id">' +
		'										<checkbox-groups :type0="k.id" @listenmsg="checkboxGetData"></checkbox-groups>' +
		'									</div>' +
		'								</div>' +
		'							</el-radio-group>' +
		'						</div>' +
		'					</div>' +
		'				</div>' +
		'				<p class="titles">Note</p>' +
		'				<textarea v-model="comment" rows="3" class="textareaBox"></textarea>' +
		'			</div>',
	props: ['reasonlist'],
	data: function() {
		return {
			reasonList1: [],
			modelList: [
				[],
				[],
				[], 'value', [],
				[],
				[]
			],
			showselsboxId: "0000", //控制展示的id
			lowerArr: [], //四级数组
			comment: '', //备注
		}
	},
	methods: {
		/*数据的方法*/
		getChangeData: function(ids) { //获取的修改数据
			this.showselsboxId = ids;
			if(this.showselsboxId == "134" || this.showselsboxId == "135" || this.showselsboxId == "136" || this.showselsboxId == "137") {
				this.lowerArr = [];
			}
			this.emititemData();
		},
		/*得到子组件的值*/
		checkboxGetData: function(obj0) { //那个偏大或偏小
			var obj = JSON.parse(JSON.stringify(obj0));
			this.lowerArr = obj.list;
			this.emititemData();
		},
		emititemData: function() { //发射当前商品数据
			var that = this;

			function fn(arr) { //数组拍平处理
				return arr.reduce(function(prev, cur) {
					return prev.concat(Array.isArray(cur) ? fn(cur) : cur)
				}, [])
			}
			var list0 = [],
				list1 = [];
			list0 = fn(that.modelList.concat(that.lowerArr));
			list0.forEach(function(item, idx, arr) {
				if(item != "value") {
					list1.push(item);
				}
			});
			that.$emit("listendate", {
				list: list1,
				comment: that.comment
			})
		},
	},
	watch: {
		reasonlist: function(val) {
			if(val == "") {
				this.reasonList1 = [];
			} else {
				this.reasonList1 = JSON.parse(val);
			}
		},
		comment: function(val) {
			this.emititemData();
		},
	},
	created: function() {

	},
	mounted: function() {
		if(this.reasonlist == "") {
			this.reasonList1 = [];
		} else {
			this.reasonList1 = JSON.parse(this.reasonlist);
		}
	},
})

Vue.component("page1-components", { //第一页组件
	template: '<div class=\'conta\'>' +
		'				<div class="conta_h3">' +
		'					<h3>Dear customer,</h3>' +
		'					<h3>Please enter e-mail address or/and order number below</h3>' +
		'				</div>' +
		'				<div>' +
		'					<div class="formBox">' +
		'						<el-input placeholder="Email:" type="email" v-model="email"></el-input>' +
		'						<el-input placeholder="Order number:" type="text" v-model="ordersn" class="input-with-select"></el-input>' +
		'						<el-button type="primary" class="elsubmit" @click="determineFn">Confirm</el-button>' +
		'					</div>' +
		'				</div>' +
		'			</div>',
	props: [],
	data: function() {
		return {
			ordersn: '',
			email: '',
		}
	},
	methods: {
		determineFn: function() {
			var that = this,
				toPageNum;

			function trim(str) {
				return str.replace(/(^\s*)|(\s*$)/g, "")
			}
			var str0 = {
				"email": trim(that.email),
				"ordersn": trim(that.ordersn)
			};
			if(str0.email != '' || str0.ordersn != '') {
				if(str0.ordersn != '') {
					toPageNum = 2;
					str0["email"]="";
				} else { //跳入选择订单页面
					toPageNum = 5;
				}
				this.$emit("pageparams", {
					"nowPageNum": 1,
					"toPageNum": toPageNum,
					"data": str0
				})
			} else {
				that.$message({
					message: 'Please enter information！',
					type: 'warning'
				});
			}

		}
	},
	watch: {},
	created: function() {},
	mounted: function() {

	},
});
Vue.component("page2-components", { //第二页组件
	template: '<div class="page2">' +
		'			<el-row>' +
		'			  <el-col :span="24">' +
		'			  	<div class="order_title">Please confirm whether it is your order.</div>' +
		'			  </el-col>' +
		'			</el-row>' +
		'			<el-row>' +
		'			  <el-col :span="24">' +
		'				<table class="table" cellpadding="0" cellspacing="0">' +
		'					<thead>' +
		'						<tr>' +
		'							<td>Order number</td>' +
		'							<td>Order amount</td>' +
		'							<td>Quantity</td>' +
		'							<td>Order time</td>' +
		'						</tr>' +
		'					</thead>' +
		'					<tbody>' +
		'						<tr>' +
		'							<td>{{orderSn}}</td>' +
		'							<td>{{orderMoney}}</td>' +
		'							<td>{{goodsNum}}</td>' +
		'							<td>{{usaPayTime}}</td>' +
		'						</tr>' +
		'					</tbody>' +
		'				</table>' +
		'			  </el-col>' +
		'			</el-row>' +
		'			<el-row :gutter="20" style="margin: 0 10px;">' +
		'  				<el-col :span="8" v-for=\'item in orderlist\'>' +
		'  					<div class="grid-content bg-purple">' +
		'	  					<div class="order_img" @click="clickImg(item.goodsSku)">' +
		'	  						<img class="order_image" :src="item.goodsThumb" />' +
		'	  					</div>' +
		'  						<div class="img_tit">' +
		'  							<div class="img_tit_box">' +
		'  								<template>' +
		'								  <el-checkbox-group v-model="selList">' +
		'								    <el-checkbox :label="item.goodsSku">{{item.goodsName}}</el-checkbox>' +
		'								  </el-checkbox-group>' +
		'								</template>' +
		'  							</div>' +
		'  						</div>' +
		'  					</div>' +
		'  				</el-col>' +
		'			</el-row>' +
		'		</div>',
	props: ["ordersn",'email'],
	data: function() {
		return {
			websiteName: '',
			selList: [], //复选商品SKU集合
			selalllist: [], //复选商品所有数据集合
			orderSn: '', //订单号
			orderMoney: '', //订单金额(包含运费)
			goodsNum: '', //订单数量
			usaPayTime: '', //下单时间
			orderlist: [], //图片--订单商品信息
		}
	},
	methods: {
		getData: function() {
			var that = this,
				datastr;
			if(this.email!=""){
				datastr={
					orderSn: that.ordersn,
					websiteName: that.websiteName,
					filter:1
				}
			}else{
				datastr={
					orderSn: that.ordersn,
					websiteName: that.websiteName,
					filter:0
				}				
			}
			this.$http.get(URL + 'v1.0/complaint/orderinfo' + PATH, {
				params: datastr,
			}, {
				emulateJSON: true
			}).then(function(res) {
				if(res.body.code != 1) {
					this.$message.warning("Pleaseentere-mailaddressor/andordernumberbelow");
					this.$emit("pageparams", {
						"nowPageNum": 2,
						"toPageNum": 1,
						"data": []
					});					
				} else {
					var orderData = res.data.orderData; //表格--订单信息
					this.orderSn = orderData.orderSn;
					this.orderMoney = orderData.orderMoney;
					this.goodsNum = orderData.goodsNum;
					this.usaPayTime = orderData.usaPayTime;
					this.orderlist = res.data.complaintOrderGoods; //图片--订单商品信息
				}
			}, function(res) {
				this.$message({ //失败提示
					showClose: true,
					message: res.data.msg,
					type: 'error'
				});
			});
		},
		clickImg:function(sku){
			var that = this,
			 	idx= that.selList.indexOf(sku);
			if(idx == -1){   //没找到
				that.selList.push(sku);
			}else{   //找到了
				that.selList.splice(idx,1);
			}
		},
	},
	watch: {
		selList: function(newval, oldval) {
			var that = this;
			that.selalllist = [];
			newval.forEach(function(cell0, idx0, arr0) {
				that.orderlist.forEach(function(cell1, idx1, arr1) {
					if(cell0 == cell1.goodsSku) {
						that.selalllist.push(cell1);
					}
				});
			});
			this.$emit("pageparams", {
				"nowPageNum": 2,
				"toPageNum": 2, //暂时不跳走
				"data": that.selalllist
			})
		}
	},
	created: function() {
		this.websiteName = get_params('websiteName');
	},
	mounted: function() {
		this.$nextTick(function() {
			//console.log(this.ordersn);
			this.getData();
		});
	},
});
Vue.component("page3-components", { //第三页组件
	template: '<div class="bodyer">' +
		'<p class="titles">please choose one or more complain treasons.</p>' +
		'	<el-collapse v-model="activeName" accordion @change="collapseFun">' +
		'		<el-collapse-item v-for="m416 in selalllist" :key="m416.goodsSku" :name="m416.goodsSku">' +
		'<template slot="title">' +
		'				<div class="goodItemBox clear">' +
		'					<div class="goodItem">' +
		'						<div class="order_img">' +
		'							<img class="img0" :src="m416.goodsThumb"/>' +
		'						</div>' +
		'						<p :attr="m416.goodsSku" class="radio">{{m416.goodsName}}</p>' +
		'					</div>' +
		'				</div>' +
		'</template>' +
		'				<p class="titles">Complain treasons</p>' +
		'				<return-reasons :reasonlist="reasonItemList" @listendate="getItemdataFn"></return-reasons>' +
		'		</el-collapse-item>' +
		'	</el-collapse>' +
		'</div>',
	props: ["selalllist"],
	data: function() {
		return {
			reasonItemList: '', //返回原因列表
			singleSubdata: { //这个非常重要
				"goodsSku": '',
				"complaintSelectOptions": '',
				"comment": ''
			},
			allSubdata: [], //所有提交数据
			activeName: '', //展开的行绑定SKU***选择商品
		}
	},
	methods: {
		getItemdataFn: function(obj) { //监听单个商品数据变化
			var that = this;
			//console.log("singleSubdata", obj);
			that.singleSubdata["complaintSelectOptions"] = obj.list;
			that.singleSubdata["comment"] = obj.comment;
			that.setAlldataFun();
		},
		setAlldataFun: function() {
			var that = this;
			if(that.allSubdata.length != 0) {
				for(var i = 0; i < that.allSubdata.length; i++) {
					if(that.allSubdata[i].goodsSku == that.singleSubdata.goodsSku) {
						that.allSubdata[i] = JSON.parse(JSON.stringify(that.singleSubdata));
						break;
					} else {
						if(i == that.allSubdata.length - 1) {
							that.allSubdata.push(that.singleSubdata);
						}
					}
				}
			} else {
				that.allSubdata.push(that.singleSubdata);
			}
		},
		collapseFun: function() {
			window.scrollTo(0,0);
		},
	},
	watch: {
		activeName: function(newval, oldval) {
			var that = this;
			that.singleSubdata = {
				"goodsSku": '',
				"complaintSelectOptions": '',
				"comment": ''
			};
			that.singleSubdata["goodsSku"] = newval;
		},
	},
	created: function() {
		var that = this;
		var loading = that.$loading({
			lock: true,
			text: 'Loading',
			spinner: 'el-icon-loading',
			background: 'rgba(0, 0, 0, 0.7)'
		});
		that.$http.get(URL + 'v1.0/complaint/alloptions' + PATH).then(function(res0) { //success
			loading.close();
			res = JSON.parse(res0.bodyText);
			that.reasonItemList = JSON.stringify(res.complaintOptions);
		}, function(res) { //error
			loading.close();
		});
	},
	mounted: function() {
		//console.log(this.selalllist);
		this.activeName = this.selalllist[0]["goodsSku"];
	},
});

Vue.component("page4-components", { //第四页组件
	template: '<div class=\'conta conta_4\'>' +
		'				<div class="imgWrap">' +
		'					<img src="img/complete.png" style="width:53px;" alt="">' +
		'				</div>' +
		'				<h3 class="h_3">Submission completed</h3>' +
		'				<div class="conta_h_3">' +
		'					<h3 style="line-height:32px">Thank you for your feedback, our customer service will contact you within 24hrs.</h3>' +
		'				</div>' +
		'				<div class="btn_wrap" v-show="false">' +
		'					<button class="priBtn">Return to the home page</button>' +
		'					<button class="colorBtn">View questionnaire information</button>' +
		'				</div>' +
		'			</div>',
	props: [],
	data: function() {
		return {

		}
	},
	methods: {

	},
	computed: {

	},
	watch: {

	},
	created: function() {

	},
	mounted: function() {

	},
});
Vue.component("page5-components", { //新增的选择订单组件
	template: '<div class="page5">' +
		'			<div class="conta_h3">' +
		'				<h3 class="h3title">Please choose one order that you want to complaint(Single choice)</h3>' +
		'			</div>' +
		'		  <el-table :data="orderList" border style="width:100%" @row-click="rowclick">' +
		'		    <el-table-column label="Order Number" align="center" min-width="118">' +
		'				<template slot-scope="scope">' +
		'				<i class="el-icon-circle-check" style="font-size:18px;color:#0eb8ef;" v-show="scope.row.select"></i><i class="selectcircle" v-show="!scope.row.select"></i>' +
		'				<span :class="{addpadding:!scope.row.select}">{{scope.row.orderSn}}</span></template>' +
		'			</el-table-column>' +
		'		    <el-table-column prop="orderMoney" label="Amount" align="center" width="70"></el-table-column>' +
		'		    <el-table-column prop="goodsNum" label="Quantity" align="center"></el-table-column>' +
		'		    <el-table-column prop="usaPayTime" label="Order time" align="center"></el-table-column>' +
		'		  </el-table>' +
		'		</div>',
	props: ["email"],
	data: function() {
		return {
			orderList: [], //订单列表
			websiteName: '',
			swt2: true, //防止抖动开关
		}
	},
	methods: {
		getOrderList: function() {
			var that = this;
			var loading = this.$loading({
				lock: true,
				text: 'Loading',
				spinner: 'el-icon-loading',
				background: 'rgba(0, 0, 0, 0.7)'
			});
			this.$http.get(URL + 'v1.0/complaint/emailordersn' + PATH, {
				params: {
					email: that.email,
					websiteName: that.websiteName
				},
			}, {
				emulateJSON: true
			}).then(function(res) {
				if(res.body.code != 1) {
					this.$message.warning(res.body.msg);
					this.$emit("pageparams", {
						"nowPageNum": 5,
						"toPageNum": 1,
						"data": ''
					});						
				} else {
					var list = res.body.orderList;
					list.forEach(function(item, idx, arr) {
						item["select"] = false;
					});
					that.orderList = list;
				}
				loading.close();
			}, function(res) {
				this.$message({ //失败提示
					showClose: true,
					message: res.data.msg,
					type: 'error'
				});
				loading.close();
			});
		},
		rowclick: function(row, column, event) {
			var that = this;
			if(this.swt2) {
				this.swt2 = false;
				that.orderList.forEach(function(item, idx, arr) {
					if(item['orderSn'] == row['orderSn']) {
						item["select"] = true;
					} else {
						item["select"] = false;
					}
				})
				this.$emit("pageparams", { //跳至选商品页面
					"nowPageNum": 5,
					"toPageNum": 5, //暂时不跳走
					"data": row['orderSn']
				})
				setTimeout(function() {
					that.swt2 = true;
				}, 200);
			}
		},
	},
	computed: {

	},
	watch: {

	},
	created: function() {
		this.websiteName = get_params('websiteName');
		this.getOrderList()
	},
	mounted: function() {

	},
});
/*********主页面home********/
new Vue({
	el: "#page",
	data: {
		showpageNum: 1, //默认展示第一页,值为1
		ordersn: '',
		email: '', //输入的邮件
		selalllist: [],
		orderId: '',
		comein5: false, //用于返回上一步是否跳入选择订单页的判断
	},
	methods: {
		monitor: function(obj) { //监听页面传出的参数
			//console.log(obj);
			var that = this;
			if(obj.nowPageNum == 1) {
				that.email = obj.data["email"];
				that.ordersn = obj.data["ordersn"];
			} else if(obj.nowPageNum == 2) {
				if(obj.data.length != 0) {
					that.selalllist = obj.data;
					that.orderId = obj.data[0].orderId;
				} else {
					that.selalllist = obj.data;
					//that.orderId = obj.data[0].orderId;
				}
			} else if(obj.nowPageNum == 5) {
				that.ordersn = obj.data;
				that.comein5 = true;
			}
			that.showpageNum = obj.toPageNum;
		},
		btnFun1: function() { //第一个btn   back
			var that = this;
			if(that.showpageNum == 2) {
				if(that.comein5) { //输入只有邮件
					that.showpageNum = 5;
				} else { //没输入订单号
					that.showpageNum = 1;
					that.ordersn = "";
					that.email = "";					
					that.comein5 = false;
				}
			} else if(that.showpageNum == 3) {
				that.showpageNum = 2;
				that.selalllist = [];
			} else if(that.showpageNum == 5) { //根据邮件选orderSn的页面
				that.showpageNum = 1;
				that.ordersn = "";
				that.email = "";
				that.comein5 = false;
			}
		},
		btnFun2: function() { //第二个btn
			var that = this;
			if(that.showpageNum == 2) { //第二页
				if(that.selalllist.length == 0) {
					that.$message({
						message: 'Please choose at least one！',
						type: 'warning'
					});
				} else {
					that.showpageNum = 3;
				}
			} else if(that.showpageNum == 3) { //第三页
				var strobj = that.$refs.page3.allSubdata,
					cansubmit = true; //判断是否可以提交
				console.log(strobj);
				if(strobj.length == 0) {
					cansubmit = false;
				} else {
					strobj.forEach(function(cell, idx, arr) {
						if(cell.complaintSelectOptions.length == 0) {
							cansubmit = false;
						}
					})
				}
				if(!cansubmit) { //选了没填
					that.$message({
						message: 'Incomplete submission of data！',
						type: 'warning'
					});
				} else if(strobj.length < that.selalllist.length) { //没选齐
					that.$message({
						message: 'Incomplete submission of data！',
						type: 'warning'
					});
				} else {
					var loading = that.$loading({
						lock: true,
						text: 'Loading',
						spinner: 'el-icon-loading',
						background: 'rgba(0, 0, 0, 0.7)'
					});
					that.$http.post(URL + 'v1.0/complaint/submit' + PATH, {
						orderId: that.orderId,
						complaintSelectOptions: JSON.stringify(strobj),
					}, {
						emulateJSON: true
					}).then(function(res0) { //success
						loading.close();
						res = JSON.parse(res0.bodyText);
						if(res.code == 1) {
							that.showpageNum = 4;
						} else {
							that.$message({
								message: res.code.msg,
								type: 'warning'
							});
						}
					}, function(res) { //error
						loading.close();
					});
				}
			} else if(that.showpageNum == 5) { //根据邮件选orderSn的页面
				if(that.ordersn != '') { //非空判断
					that.showpageNum = 2;
				} else {
					that.$message({
						message: "Please choose！",
						type: 'warning'
					});
				}
			}
		},
	},
	computed: {

	},
	watch: {
		showpageNum: function(newval, oldval) { //监听展示页面参数变化

		},
	},
	created: function() {

	},
	mounted: function() {

	},
})