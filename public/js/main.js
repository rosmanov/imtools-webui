var imToolsWebUi = {
    mergeProgressNodeId: 'merge-progress',

    deleteObject: function(url, node) {
        $.get(url, function(data) {})
        .done(function() {
            $(node).remove();
        })
        .fail(function(xhr, error) {
            alert("Failed to delete image: " + error);
        });
        return false;
    },

    /**
     * @param Node node Node which will be replaced with new thumbnail
     * @param Variant wsCmd WebSocket Server command data in JSON (`Object` or `String`)
     * @param Integer imageId Source image ID
     * @param Integer formatId Thumbnail format ID
     * @return Boolean false
     */
    makeThumbnail: function(node, wsCmd, imageId, formatId) {
        wc.sendMessage(wsCmd, function(evt) {
            wc._defaultOnMessage.call(wc, evt);

            var data = JSON.parse(evt.data);
            if (!data || data.error != false) {
                $(node).parent().append("<br><span style='color:red'>Command failed!</span>");
                return;
            }

            // Thumbnail file is ready. Now add it to database.
            $.get('/api.php?action=add_thumb&image-id=' + imageId + '&format-id=' + formatId
                  + '&path=' + encodeURIComponent(wsCmd.arguments.output),
                  function (d) {
                      if (d.error) {
                          $(node).parent().append("<br><span style='color:red'>" + d.response + "</span>");
                          return;
                      }

                      var src = wsCmd.arguments.output
                      .replace(/^.*\/([^\/]+\.[a-z]{3,4})$/, '/uploads/$1');
                      $(node).parent().html("<img src='" + src + "'/>");
                  }
            );
        });

        return false;
    },

    /**
     * Sends 'merge' command through the WebSocket server client
     *
     * @return Boolean false
     */
    mergePreview: function(data, container) {
        var
        wsPreviewCmd   = data.ws_preview_command,
        wsRealMergeCmd = data.ws_real_merge_command,
        previewDir     = data.preview_dir,
        imageId        = data.image_id;

        $(container).html('<pre id="' + imToolsWebUi.mergeProgressNodeId + '" class="wsclient-output"><p>Working...</p></pre>');

        wc.sendMessage(wsPreviewCmd, function (evt) {
            var data;

            wc._defaultOnMessage.call(wc, evt);

            if (! (data = JSON.parse(evt.data))) {
                imToolsWebUi._log(container, 'JSON.parse(' + evt.data + ') failed', 'error');
                return;
            }

            if (typeof data.type != 'undefined' && data.type == 3) {
                imToolsWebUi._logMergeEvent(data.response);
                return;
            }

            if (data.error != false) {
                imToolsWebUi._log(container, 'Command failed' + JSON.stringify(wsPreviewCmd), 'error');
                return;
            }

            imToolsWebUi.getMergePreview(wsPreviewCmd, imageId, previewDir, function(d) {
                $(container)
                .append(d.response)
                .find('form')
                .submit(function(e) {
                    imToolsWebUi.merge(wsRealMergeCmd, container);
                    e.preventDefault();
                });
            });

        });

        return false;
    },

    _logMergeEvent: function(message) {
        $('#' + imToolsWebUi.mergeProgressNodeId).append("<p>" + message + "</p>");
    },

    merge: function(wsCmd, container) {
        wc.sendMessage(wsCmd, function(evt) {
            var data;

            wc._defaultOnMessage.call(wc, evt);

            if (! (data = JSON.parse(evt.data))) {
                imToolsWebUi._log(container, 'failed to parse JSON', 'error');
                return;
            }

            if (data.error != false) {
                imToolsWebUi._log(container, 'Merge failed :' + d.response, 'error');
                return;
            }

            if (typeof data.type != 'undefined' && data.type == 3) {
                imToolsWebUi._logMergeEvent(data.response);
                return;
            }

            imToolsWebUi._log(container, 'Merged successfully!', 'success');
        });
    },

    diff: function(wsCmd, url) {
        wc.sendMessage(wsCmd, function(evt) {
            var data;

            wc._defaultOnMessage.call(wc, evt);

            if (! (data = JSON.parse(evt.data))) {
                imToolsWebUi._log('failed to parse JSON');
                return;
            }

            if (data.error != false) {
                imToolsWebUi._log('Diff failed: ' + d.response);
                return;
            }

            $.magnificPopup.open({
                items : { src: url },
                type: 'image'
            });
        });
    },

    poll : function() {
        wc.sendMessage({type: 2}, function(evt) {
            var data;

            wc._defaultOnMessage.call(wc, evt);

            if (! (data = JSON.parse(evt.data))) {
                imToolsWebUi._log('failed to parse JSON');
                return;
            }

            if (data.error != false) {
                imToolsWebUi._log('Diff failed: ' + d.response);
                return;
            }
        });
    },

    getMergePreview: function(wsPreviewCmd, imageId, previewDir, callback) {
        var html;
        var imageIds = Object.keys(wsPreviewCmd.arguments.input_images).join(',');

        // Remove source image id
        var idx = imageIds.indexOf(imageId);
        if (idx != -1) {
            imageIds.splice(idx, 1);
        }

        var postData = {
            'image_ids': imageIds,
            'preview_dir': previewDir
        };

        $.post('/api.php?action=op_get_merge_preview', postData, callback);

        return html;
    },

    deleteOperation: function(operationId) {
       localStorage.removeItem('operation' + operationId);
    },

    _log : function(container, message, state) {
        if (state == 'error') {
            message = "<span style='color: darkred'>" + message + "</span>";
        } else if (state == 'success') {
            message = "<span style='color: green'>" + message + "</span>";
        }
        $(container).append(message);
    }
};
