define(['jquery', 'bootstrap', 'backend', 'table', 'form','selectpage'], function ($, undefined, Backend, Table, Form,selectPage) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    kefu_url: 'user/user/kefu',
                    parent_url: 'user/user/parent',
                    money_url: 'user/user/money',
                    brand_url: 'user/user/brand',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true,operate:false},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'parent_name', title: __('上级分销商'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'score', title: __('Score'), operate: 'BETWEEN', sortable: true},
                        {field: 'amount', title: __('额度')},
                        {field: 'admin_id', title: __('所属客服')},
                        {field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'jointime', title: __('Jointime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('冻结')}},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate
                            ,buttons: [
                                {
                                    name: 'ajax',
                                    text: __(''),
                                    title: __('冻结'),
                                    classname: 'btn btn-xs btn-danger btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: "user/user/status?status=hidden",
                                    confirm: '确认要冻结吗？',
                                    success: function (data, ret) {
                                        var opt = {
                                            url: '',
                                        };
                                        table.bootstrapTable('refresh', opt);
                                        return true;
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    },
                                    visible: function(row) {
                                        // 编辑按钮 动态是否显示
                                        if (row.status !== 'normal') {
                                            return false;
                                        }
                                        return true;
                                    }
                                },{
                                    name: 'ajax',
                                    text: __(''),
                                    title: __('解冻'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: "user/user/status?status=normal",
                                    confirm: '确认要解冻吗？',
                                    success: function (data, ret) {
                                        var opt = {
                                            url: '',
                                        };
                                        table.bootstrapTable('refresh', opt);
                                        return true;
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    },
                                    visible: function(row) {
                                        // 编辑按钮 动态是否显示
                                        if (row.status !== 'hidden') {
                                            return false;
                                        }
                                        return true;
                                    }
                                },{
                                    name: 'kefu',
                                    title: __('设置客服'),
                                    extend:'data-area=["800px","400px"]',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-gear',
                                    url: 'user/user/kefu',
                                    callback: function (data) {
                                        // Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                },{
                                    name: 'money',
                                    title: __('设置额度'),
                                    extend:'data-area=["800px","400px"]',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-jpy',
                                    url: 'user/user/money',
                                    callback: function (data) {
                                        // Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                },{
                                    name: 'parent',
                                    title: __('设置上级分销账号'),
                                    extend:'data-area=["800px","400px"]',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-user-plus',
                                    url: 'user/user/parent',
                                    callback: function (data) {
                                        // Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                    visible: function(row) {
                                        // 编辑按钮 动态是否显示
                                        if (row.pid !== 0) {
                                            return false;
                                        }
                                        return true;
                                    }
                                },{
                                    name: 'brand',
                                    title: __('设置品牌折扣'),
                                    extend:'data-area=["800px","400px"]',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-connectdevelop',
                                    url: 'user/user/brand',
                                    callback: function (data) {
                                        // Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                }],
                            formatter:function (value, row, index) {
                                var that = $.extend({}, this);
                                $(table).data("operate-edit", null); // 列表页面隐藏 .编辑operate-edit  - 删除按钮operate-del
                                $(table).data("operate-del", null); // 列表页面隐藏 .编辑operate-edit  - 删除按钮operate-del
                                that.table = table;
                                return Table.api.formatter.operate.call(that, value, row, index);
                            }},

                    ]
                ]
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        kefu: function () {
            Controller.api.bindevent();
        },
        parent: function () {
            Controller.api.bindevent();
        },
        money: function () {
            Controller.api.bindevent();
        },
        brand: function () {
            $(document).on("fa.event.appendfieldlist", "#second-table .btn-append", function (e, obj) {
                //绑定动态下拉组件
                Form.events.selectpage(obj);
            });
            Controller.api.bindevent();
        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});