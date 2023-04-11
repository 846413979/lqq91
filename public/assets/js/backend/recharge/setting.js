define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'recharge/setting/index' + location.search,
                    add_url: 'recharge/setting/add',
                    edit_url: 'recharge/setting/edit',
                    del_url: 'recharge/setting/del',
                    multi_url: 'recharge/setting/multi',
                    import_url: 'recharge/setting/import',
                    table: 'recharge_setting',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'presented_money', title: __('Presented_money'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            $(document).on("fa.event.appendfieldlist", "#second-table .btn-append", function (e, obj) {
                //绑定动态下拉组件
                Form.events.selectpage(obj);
            });
            Controller.api.bindevent();
        },
        edit: function () {
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
