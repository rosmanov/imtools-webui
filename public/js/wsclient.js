/* ImTools WebSocket server client */
wsClient = function(host, port, onOpen, outputNode) {
    var self = this;

    self.jqOutputNode = $(outputNode);
    self.connected = false;
    self.host = host;
    self.port = Number(port);
    self.uri = "ws://" + host + ":" + port;
    self.websocket = new WebSocket(self.uri);

    self.websocket.onopen = function (evt) {
        self.connected = true;
        if (typeof onOpen == 'function' ) {
            onOpen(evt);
        } else {
            self._defaultOnOpen.call(self, evt);
        }
    };
    self.websocket.onerror = function (evt) {
        self._defaultOnError.call(self, evt);
    };
};

wsClient.prototype._defaultOnOpen = function (evt) {
    if (!this.connected) {
        this.log("<p style='color: red'>Failed to connect to " + this.uri + "</p>");
    } else {
        this.log("<p style='color: orange'>Connected to " + this.uri + "</p>");
    }
};

wsClient.prototype._defaultOnError = function (evt) {
    var msg,
    ws = this instanceof WebSocket ? this : this.websocket;

    if (evt.data)
        msg = evt.data;
    else if (ws.readyState == 0)
        msg = "Failed to open connection"
    else if (ws.readyState == 3)
        msg = "Connection closed"
    else
        msg = 'unknown';

    msg += ", URI: " + this.uri;

    this.log("<p style='color: #E43E3E;'>&gt; ERROR: " + msg + "</p>");
};

wsClient.prototype._defaultOnMessage = function (evt) {
    this.log("<p style='color: rgb(151, 213, 151);'>&gt; RESPONSE: " + evt.data + "</p>");
};


/**
 * @brief Sends message to the WebSocket server
 *
 * @param Variant message Server command in JSON (whether `Object`, or `String`)
 * @param Function onMessage
 * @param Function onError
 *
 * @return Boolean
 */
wsClient.prototype.sendMessage = function (message, onMessage, onError) {
    var self = this;

    if (!this.connected) {
        return false;
    }


    if (typeof onMessage == 'function') {
        this.websocket.onmessage = onMessage;
    } else {
        this.websocket.onmessage = function (evt) {
            self._defaultOnMessage.call(self, evt);
        };
    }
    if (typeof onError == 'function') {
        this.websocket.onerror = onError;
    } else {
        this.websocket.onerror = function (evt) {
            self._defaultOnError.call(self, evt);
        };
    }

    if (typeof message == 'object') {
        message = JSON.stringify(message, null, '  ');
    }

    this.websocket.send(message);
    this.log("<p>&gt; SENT: " + message + "</p>");
    return true;
};

wsClient.prototype.log = function (message) {
    var n = this.jqOutputNode;
    n.html(this.jqOutputNode.html() + message);
    var h = n[0].scrollHeight;
    n.animate({scrollTop:h}, {
        duration: 100
    });
}
