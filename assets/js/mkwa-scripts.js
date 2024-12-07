jQuery(document).ready(function($) {
    $('.mkwa-daily-quests').on('click', '.complete-quest', function() {
        var button = $(this);
        var questId = button.closest('li').data('quest-id');
        $.ajax({
            url: mkwaAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'mkwa_complete_quest',
                quest_id: questId
            },
            success: function(response) {
                if (response.success) {
                    button.replaceWith('<span class="completed">Completed</span>');
                } else {
                    alert(response.data);
                }
            }
        });
    });
});
