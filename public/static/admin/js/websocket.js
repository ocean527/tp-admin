layui.define(["jquery"],function(exports){
    const $ = layui.jquery;

    var websocket = function() {};
    
    websocket.prototype.settings = {
        "url":"",
        "heartbeat":54000,   //心跳周期，单位毫秒
        "onclose":null,
        "onerror":null,
        "onopen":null,
        "onmessage":null,
    };
    
    websocket.prototype.init = function(options) {
        var ws = null;  
        var lockReconnect = false;  //避免ws重复连接
        var that = this;
        
        $.extend(that.settings, options);
        
        var createWebSocket = function(url) {
            try{
                if('WebSocket' in window){
                    ws = new WebSocket(url);
                }else if('MozWebSocket' in window){  
                    ws = new MozWebSocket(url);
                }else{
                    layer.alert("您的浏览器不支持websocket协议,建议使用新版谷歌、火狐等浏览器，请勿使用IE10以下浏览器，360浏览器请使用极速模式，不要使用兼容模式！"); 
                }
                initEventHandle();
            }catch(e){
                reconnect(url);
                console.log(e);
            }
        };

        var initEventHandle = function() {           
            window.onbeforeunload = function() {
                ws.close();
            };
            ws.onclose = function () {
                reconnect(that.settings.url);
                (typeof that.settings.onclose === "function") && that.settings.onclose();
            };
            ws.onerror = function () {
                reconnect(that.settings.url);
                (typeof that.settings.onerror === "function") && that.settings.onerror();
            };
            ws.onopen = function () {
                heartCheck.reset().start();      //心跳检测重置
                (typeof that.settings.onopen === "function") && that.settings.onopen();
            };
            ws.onmessage = function (event) {    //如果获取到消息，心跳检测重置
                heartCheck.reset().start();      //拿到任何消息都说明当前连接是正常的
                if(event.data!='pong'){
                    (typeof that.settings.onmessage) && that.settings.onmessage(event.data);
                }
            };
        };
        
        var reconnect = function(url) {
            if(lockReconnect) return;
            lockReconnect = true;
            setTimeout(function () {     //没连接上会一直重连，设置延迟避免请求过多
                createWebSocket(that.settings.url);
                lockReconnect = false;
            }, 2000);
        };

        var heartCheck = {
            timeout: that.settings.heartbeat,
            timeoutObj: null,
            serverTimeoutObj: null,
            reset: function(){
                clearTimeout(this.timeoutObj);
                clearTimeout(this.serverTimeoutObj);
                return this;
            },
            start: function(){
                var self = this;
                this.timeoutObj = setTimeout(function(){
                    //这里发送一个心跳，后端收到后，返回一个心跳消息，
                    //onmessage拿到返回的心跳就说明连接正常
                    ws.send("ping");
                    self.serverTimeoutObj = setTimeout(function(){//如果超过一定时间还没重置，说明后端主动断开了
                        ws.close();     //如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
                    }, self.timeout)
                }, this.timeout)
            }
        };
        
        createWebSocket(that.settings.url);
    };
    
    exports("websocket", new websocket());
});