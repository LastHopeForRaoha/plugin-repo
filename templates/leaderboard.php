<div class="leaderboard-container">
    <h2>Leaderboard</h2>
    <ol>
        <?php
        foreach ($leaderboard_data as $rank => $user) {
            echo "<li>" . $user['name'] . " - Points: " . $user['points'] . "</li>";
        }
        ?>
    </ol>
</div>
