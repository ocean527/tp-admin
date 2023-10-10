layui.define(['jquery','utils','form'],function(exports) {
    const $ = layui.jquery;
    const utils = layui.utils;
    const form = layui.form;
    const _MOD = "asyncSelect";
    
    var AsyncSelect = function() {
        this.version = "1.0.0";
    }
    
    AsyncSelect.prototype.default = {
        elem : undefined,
        url : undefined,
        idCol: 'id',
        textCol: 'text',
        where:{},
        done:undefined
    }
    
    AsyncSelect.prototype.render = function(options) {
        const that = this;
        var settings = {};
        $.extend(settings, that.default, options);
        var elemObj = $(settings.elem);
        if (elemObj.length < 1) {
            utils.error('Not found the specified element');
            return that;
        }
        if (!elemObj.is("select")) {
            utils.error('The specified element is not select');
            return that;
        }
        if (!utils.isString(settings.url)) {
            utils.error('Url error');
            return that;
        }
        $.post(settings.url,settings.where,function(data) {
            var optionsStr = '<option value=""></option>';
            for (let i = 0; i < data.length; i++) {
                optionsStr += '<option value="'+data[i][settings.idCol]+'">'+data[i][settings.textCol]+'</option>'
            }
            elemObj.html(optionsStr);
            //设置默认值
            if (elemObj.attr("data-defaultValue") !== undefined) {
                elemObj.val(elemObj.attr("data-defaultValue"));
            }
            form.render("select");
            typeof settings.done === 'function' && settings.done();
        }, 'json');
    }
    
    const asyncSelect = new AsyncSelect();
    exports(_MOD, asyncSelect);
});