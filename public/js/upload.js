var itUploader = {
    settings: {
        dropZone: '#drop',
        _uploadContainer: '#upload',
        done: function(e, data) {},
        fail: function(e, data) { data.context.addClass('error'); }
    },

    init: function(s) {
        var self = this;

        if (s.hasOwnProperty('dropZone')) self.settings.dropZone = s.dropZone;
        if (s.hasOwnProperty('uploadContainer')) self.settings._uploadContainer = s.uploadContainer;
        if (s.hasOwnProperty('done')) self.settings.done = s.done;
        if (s.hasOwnProperty('fail')) self.settings.fail = s.fail;

        var ul = $(self.settings._uploadContainer + ' ul');

        $(self.settings.dropZone + ' a').click(function() {
            $(this).parent().find('input').click();
        });

        $(self.settings._uploadContainer).fileupload({
            dropZone: $(self.settings.dropZone),
            add: function (e, data) {
                var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"'+
                            ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p></p><span></span></li>');
                tpl.find('p').text(data.files[0].name)
                .append('<i>' + self.formatFileSize(data.files[0].size) + '</i>');
                data.context = tpl.appendTo(ul);

                tpl.find('span').click(function() {
                    if (tpl.hasClass('working')) {
                        jqXHR.abort();
                    }
                    tpl.fadeOut(function() {
                        tpl.remove();
                    });
                });

                // Poll whether we can remove tpl
                var timer = setInterval(function() {
                    if (tpl.hasClass('working')) return;
                    tpl.fadeOut(function() {
                        tpl.remove();
                    });
                    clearTimeout(timer);
                }, 6e3);

                // Automatically upload the file once it is added to the queue
                var jqXHR = data.submit();
            },
            progress: function(e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                data.context.find('input').val(progress).change();
                if (progress == 100) {
                    data.context.removeClass('working');
                }
            },
            fail : self.settings.fail,
            done: self.settings.done
        });

        $(document).on('drop dragover', function (e) {
            e.preventDefault();
        });
    },

    formatFileSize: function(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }
        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }
        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }
        return (bytes / 1000).toFixed(2) + ' KB';
    }
};
