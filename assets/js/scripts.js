jQuery(document).ready(function($) {
    // Profile Update
    $('#mkwa-edit-profile').on('click', function() {
        const newUsername = prompt('Enter a new username:');
        if (newUsername) {
            $.post(ajaxurl, {
                action: 'mkwa_update_profile',
                username: newUsername,
                security: mkwa_ajax.security
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Failed to update profile.');
                }
            });
        }
    });

    // Leaderboard Opt-Out
    $('#mkwa-optout-leaderboard').on('change', function() {
        const optOut = $(this).is(':checked');
        $.post(ajaxurl, {
            action: 'mkwa_update_leaderboard_optout',
            optout: optOut,
            security: mkwa_ajax.security
        }, function(response) {
            if (response.success) {
                alert('Your leaderboard preferences have been updated.');
            } else {
                alert('Failed to update preferences.');
            }
        });
    });

    // Rewards Redemption
    $('.redeem-reward').on('click', function() {
        const rewardId = $(this).data('reward');
        if (confirm('Are you sure you want to redeem this reward?')) {
            $.post(ajaxurl, {
                action: 'mkwa_redeem_reward',
                reward_id: rewardId,
                security: mkwa_ajax.security
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            });
        }
    });
});
