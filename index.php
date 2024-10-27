<?php
require_once 'config.php';
require_once 'functions.php';

// Get today's date in the format stored in the database
$today = date('Y-m-d');

// Fetch today's match tips
$today_tips_query = "SELECT t.*, m.home_team, m.away_team, m.commence_time 
                     FROM tips t
                     JOIN matches m ON t.match_name = m.match_name
                     WHERE DATE(m.commence_time) = ?
                     ORDER BY m.commence_time ASC 
                     LIMIT 3";

$stmt = $conn->prepare($today_tips_query);
$stmt->bind_param("s", $today);
$stmt->execute();
$today_tips = $stmt->get_result();

// Fetch all news items and randomize their order
$all_news = $conn->query("SELECT * FROM news ORDER BY RAND()");
$latest_news = $all_news->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<section class="hero" style="background-image: url('assets/hero-background.png'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="hero-content" style="background-color: rgba(0, 0, 0, 0.6); padding: 2rem; border-radius: 10px;">
            <h1 style="color: white;">Expert Betting Tips</h1>
            <p style="color: white;">Get the latest insights and predictions for today's matches from our team of betting experts.</p>
            <a href="#todays-tips" class="cta-button" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">View Today's Match Tips</a>
        </div>
    </div>
</section>

<section id="todays-tips" class="section">
    <div class="container">
        <h2 class="section-title">Today's Match Tips</h2>
        <div class="grid">
            <?php if ($today_tips->num_rows > 0): ?>
                <?php while ($tip = $today_tips->fetch_assoc()): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($tip['home_team'] . ' vs ' . $tip['away_team']); ?></h3>
                        <p class="match-time"><?php echo date('H:i', strtotime($tip['commence_time'])); ?></p>
                        <?php 
                        $tip_labels = [
                            '1' => 'Home Win (1)',
                            'X' => 'Draw (X)',
                            '2' => 'Away Win (2)',
                            '1X' => 'Home Win or Draw (1X)',
                            '12' => 'Home or Away Win (12)',
                            'X2' => 'Draw or Away Win (X2)',
                            'BTTS' => 'Both Teams To Score',
                            'O2.5' => 'Over 2.5 Goals',
                            'U2.5' => 'Under 2.5 Goals'
                        ];
                        $tips_array = json_decode($tip['tips'], true);
                        ?>
                        <ul>
                            <?php foreach ($tips_array as $single_tip): ?>
                                <li><?php echo htmlspecialchars($tip_labels[$single_tip] ?? $single_tip); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="tip.php?id=<?php echo $tip['id']; ?>" class="cta-button">View Full Tip</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No match tips available for today. Check back later!</p>
            <?php endif; ?>
        </div>
    </div>  
</section>

<section id="latest-news" class="section">
    <div class="container">
        <h2 class="section-title">Latest News</h2>
        <div class="news-carousel-container">
            <div class="news-carousel">
                <?php foreach ($latest_news as $news): ?>
                    <div class="news-card">
                        <img src="<?php echo htmlspecialchars($news['image_url']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="news-image">
                        <div class="news-content">
                            <p class="news-date"><?php echo date('Y. M. d.', strtotime($news['created_at'])); ?></p>
                            <h3 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                            <a href="<?php echo htmlspecialchars($news['link']); ?>" class="news-link">Read More</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newsCarousel = document.querySelector('.news-carousel');
    const newsCards = document.querySelectorAll('.news-card');
    const cardWidth = newsCards[0].offsetWidth + 20;
    let currentIndex = 0;

    // Clone all cards and append them to the end for smooth infinite looping
    newsCards.forEach(card => {
        const cardClone = card.cloneNode(true);
        newsCarousel.appendChild(cardClone);
    });

    function scrollToNextCard() {
        currentIndex++;
        newsCarousel.style.transition = 'transform 0.5s ease';
        newsCarousel.style.transform = `translateX(-${currentIndex * cardWidth}px)`;

        if (currentIndex >= newsCards.length) {
            setTimeout(() => {
                newsCarousel.style.transition = 'none';
                newsCarousel.style.transform = 'translateX(0)';
                currentIndex = 0;
            }, 500);
        }
    }

    setInterval(scrollToNextCard, 5000); // Change card every 5 seconds
});
</script>

<?php include 'footer.php'; ?>