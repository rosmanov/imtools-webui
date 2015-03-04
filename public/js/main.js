var imToolsWebUi = {
    deleteObject: function(url, node) {
        $.get(url, function(data) {})
        .done(function() {
            $(node).remove();
        })
        .fail(function(xhr, error) {
            alert("Failed to delete image: " + error);
        });
        return false;
    }
};
