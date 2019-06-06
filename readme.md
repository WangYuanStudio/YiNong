## 项目说明

	这是n年前网园的一个外包项目，是一家小型公司的产品宣传网站。云龙桑负责前端，悠风负责后端。后因客户需求有变动，该项目解决方案最终未采用，现在把这个项目放在GitHub这里，以便后来人参考。

## 前后端接口文档


### // 登录接口

1. 管理员登录

   >路径：./server/admin.php
   >
   >方法：POST
   >
   >发送：`{action: 'login', username: 'xxx', password: 'xxx'}`
   >
   >返回：`{code: x, msg: 'xxx', token: 'xxx'}`
   >
   >	code:	返回代码：0表示登录失败，1表示登录成功。
   >	
   >	msg:	返回消息：若code为0则返回错误消息。
   >	
   >	token:	token验证码，code为1时有效。
   >
   >注明：前端应记录返回的token值，用于其他操作的验证标识。

2. 验证是否已登录

   >路径：./server/admin.php
   >
   >方法：POST
   >
   >发送：`{action: 'verify', token: 'xxx'}`
   >
   >返回：`{code: x}`
   >
   >	code:	返回代码：0表示未登录或token错误，1表示已登录。

3. 退出登录

   >路径：./server/admin.php
   >
   >方法：POST
   >
   >发送：`{action: 'logout', token: 'xxx'}`
   >
   >返回：`{code: x, msg: 'xxx'}`
   >
   >	code:	返回代码：0表示退出失败或token错误，1表示退出成功。
   >	
   >	msg:	返回消息：code为0时返回错误消息。

4. 修改信息

   >路径：./server/admin.php
   >
   >方法：POST
   >
   >发送：`{action: 'change', token: 'xxx', old_password: 'xxx', new_username: 'xxx', new_password: 'xxx'}`
   >
   >	old_password:	旧密码。
   >	
   >	new_username:	新用户名，若不修改用户名则缺省。
   >	
   >	new_password:	新密码。
   >
   >返回：`{code: x, msg: 'xxx'}`
   >
   >	code:	返回代码：0表示修改失败，1表示修改成功。
   >	
   >	msg：	返回消息：code为0时返回错误消息。
   >
   >注明：管理员用户信息修改成功后会自动注销当前token，前端需要跳回到登录页面让用户重新登录重新生成token。




### // 产品接口

1. 发布新品

   >路径：./server/product.php
   >
   >方法：POST
   >
   >发送：`{action: 'addproduct', token: 'xxx', proname: 'xxx', description: 'xxx', picurl: 'xxx', sortid: xxx}`
   >
   >	proname:	产品名称.
   >	
   >	description:	产品描述。
   >	
   >	picurl:		产品图片地址。
   >	
   >	sortid:		分类ID。
   >
   >返回：`{code: x, msg: 'xxx'}`
   >
   >	code:	返回代码：发布失败返回0，成功则返回产品ID。
   >	
   >	msg:	返回消息：如果code为0则返回错误消息。
   >
   >注明：在请求之前应先上传产品图片。

2. 修改产品信息

   >路径：./server/product.php
   >
   >方法：POST
   >
   >发送：`{action: 'editproduct', token: 'xxx', proid: xxx, proname: 'xxx', description: 'xxx', picurl: 'xxx', sortid: xxx}`
   >
   >	proid:		产品ID，必填。
   >	
   >	proname:	产品名称，若不修改名称可缺省。
   >	
   >	description:	产品描述，若不修改描述可缺省。
   >	
   >	picurl:		产品图片地址，若不修改图片地址可缺省。
   >	
   >	sortid:		分类ID，若不修改分类可缺省。
   >
   >返回：`{code: x, msg: 'xxx'}`
   >
   >	code:	返回代码：0表示失败，1表示成功。
   >	
   >	msg:	返回消息：如果code为0则返回错误消息。
   >
   >注明：产品信息proname、description、picurl、sortid中应至少有一项非空。

3. 删除产品

   >路径：./server/product.php
   >
   >方法：POST
   >
   >发送：`{action: 'deleteproduct', token: 'xxx', proid: xxx}`
   >
   >返回：`{code: x, msg: 'xxx'}`
   >
   >	code:	返回代码：0表示失败，1表示成功。
   >	
   >	msg:	返回消息：如果code为0则返回错误消息。

4. 上传产品图片

   > 路径：./server/product.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'uploadpic', token: 'xxx'}`
   >
   > 	   `<input type="file" name="propic" /> `
   >
   > 返回：`{code: x, msg: 'xxx'}`
   >
   > 	code:	返回代码：0表示上传失败，1表示上传成功。
   > 	
   > 	msg:	返回消息：若code为0则返回错误消息，若code为1则返回图片地址。
   >
   > 注明：仅支持JPG、JPEG、GIF、PNG格式，且文件小于1M。

5. 检查产品名称是否已存在

   > 路径：./server/product.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'check', proname: 'xxx', proid: xxx}`
   >
   > 返回：`{code: x, msg: 'xxx'}`
   >
   > 	code:	返回代码：0表示产品不存在，1表示产品存在，2表示请求出错。
   > 	
   > 	msg:	返回消息：如果code为2则返回错误消息。
   >
   > 注明：proname和proid只需填其中一个，另一个留空，若两个都填，默认检查proname。

6. 获取产品列表/搜索产品

   >路径：./server/product.php
   >
   >方法：POST
   >
   >发送：`{action: 'getlist', pagesize: xxx, pageindex: xxx, sortid: xxx, searchtext: 'xxx'}`
   >
   >	pagesize:	定义每一页显示的产品数量，若缺省，则默认为获取所有产品，即不分页；若获取到的对应页面的产品数量n不足pagesize，则只返回n个产品信息。
   >	
   >	pageindex:	定义分页索引，从1开始；当pagesize缺省时，此参数无效。
   >	
   >	sortid:		定义分类ID，若缺省，则默认获取所有分类的产品。
   >	
   >	searchtext:	欲搜索的关键字，若缺省，则表示不搜索。
   >
   >返回：`{code: x, msg: 'xxx', list: [{proid: xxx, name: 'xxx', description: 'xxx', picurl: 'xxx', sortid: xxx, createtime: xxxx-xx-xx}, {proid: xxx, name: 'xxx', description: '也xxx', picurl: 'xxx', sortid: xxx, createtime: xxxx-xx-xx}, {...}]}`
   >
   >	code:	返回代码：获取/搜索无数据时或请求出错时返回0，获取/搜索到有数据时则返回以pagesize分页的总页码数。
   >	
   >	msg:	返回消息：若code为0则返回错误消息，若code为1则返回页面总数。
   >	
   >	list:		code为1时有效，为返回的产品列表数据，二维JSON数据，包含proid、name、description、picurl、sortid和createtime。
   >
   >注明：产品列表以最新发布时间排序。前端调用第一页时应记录返回的总页码数，方便分页设计。

7. 获取分类列表

   >路径：./server/product.php
   >
   >方法：POST
   >
   >发送：`{action: 'getsort'}`
   >
   >返回：`{code: x, msg: 'xxx', list: [{sortid: xxx, sortname: 'xxx'}, {sortid: xxx, sortname: 'xxx'}, {...}]}`
   >
   >	code:	返回代码：0表示失败，1表示成功。
   >	
   >	msg:	返回消息：如果code为0则返回错误消息。
   >	
   >	list:		code为1时有效，为返回的分类列表数据，二维JSON数据，包含sortid和sortname。

8. 添加分类

   > 路径：./server/product.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'addsort', token: 'xxx', name: 'xxx'}`
   >
   > 返回：`{code: x, msg: 'xxx'}`
   >
   > 	code: 	返回代码：失败返回0，成功返回分类ID。
   > 	
   > 	msg:	返回消息：如果code为0则返回错误消息。

9. 修改分类

   > 路径：./server/product.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'editsort', token: 'xxxx', sortid: xxx, newname: 'xxx'}`
   >
   > 返回：`{code: x, msg: 'xxx'}`
   >
   > 	code:	返回代码：0表示失败，1表示成功。
   > 	
   > 	msg:	返回消息：如果code为0则返回错误消息。

10. 删除分类

    > 路径：./server/product.php
    >
    > 方法：POST
    >
    > 发送：`{action: 'deletesort', token: 'xxx', sortid: xxx}`
    >
    > 返回：`{code: x, msg: 'xxx'}`
    >
    > 	code: 	返回代码：0表示失败，1表示成功。
    > 	
    > 	msg:	返回消息：如果code为0则返回错误消息。

### // 公司信息接口

1. 上传轮播图片

   > 路径：./server/company.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'uploadpic', token: 'xxx', picid: x}`
   >
   > 	 `<input type="file" name="picture" /> `
   > 	
   > 	picid:	轮播图索引ID，其值为1/2/3，分别对应三张轮播图。
   >
   > 返回：`{code: x, msg: 'xxx'}`
   >
   > 	code:	返回代码：0表示上传失败，1表示上传成功。
   > 	
   > 	msg:	返回消息：若code为0则返回错误消息，若code为1则返回图片地址。
   >
   > 注明：仅支持JPG、JPEG、GIF、PNG格式，且文件小于2M。

2. 获取轮播图地址

   > 路径：./server/company.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'getpicurl', picid: x}`
   >
   > 返回：`{code: x, msg: 'xxx'}`
   >
   > 	code:	返回代码：0表示失败，1表示成功。
   > 	
   > 	msg:	返回消息：若code为0则返回错误消息，若code为1则返回对应的轮播图地址。

3. 修改公司简介信息

   > 路径：./server/company.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'editintro', token: 'xxx', newintro: 'xxx'}`
   >
   > 返回：`{code: x, msg: 'xxx'}`
   >
   > 	code:	返回代码：0表示失败，1表示成功。
   > 	
   > 	errmsg:	返回消息：如果code为0则返回错误消息。

4. 获取公司简介信息

   > 路径：./server/company.php
   >
   > 方法：POST
   >
   > 发送：`{action: 'getintro'}`
   >
   > 返回：`{content: 'xxx'}`
   >
   > 	content:	获取到的公司简介信息内容，如果获取失败则返回空文本。