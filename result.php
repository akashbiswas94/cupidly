<?php
require "db.php";
$code = $_GET["c"] ?? "";

$stmt = $conn->prepare("SELECT * FROM proposals WHERE private_code=?");
$stmt->bind_param("s",$code);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();

if(!$p) die("Invalid link");

$status = $p["status"];
$viewed = $p["viewed_at"];

// Prepare Share Content
$shareLink = "https://yesly.online"; 
$shareText = "Target Acquired! 🥂 My Valentine's proposal on Yesly was a success. Create yours here: " . $shareLink;
$encodedText = urlencode($shareText);

// Default: Pending
$config = [
    'theme' => '#f1c40f',
    'bg' => 'linear-gradient(135deg, #fef9e7, #fcf3cf)',
    'emoji' => '🙄',
    'live_emojis' => ['⏳', '🙄', '❓', '👻'],
    'commentary' => "The link is out there. You're just sitting here staring at a screen. Get a hobby, maybe? 🙄",
    'stamp' => 'Unopened'
];

if ($status == "pending" && $viewed) {
    $config = [
        'theme' => '#95a5a6',
        'bg' => 'linear-gradient(135deg, #f2f4f4, #e5e8e8)',
        'emoji' => '💀',
        'live_emojis' => ['💀', '☁️', '💨', '😶'],
        'commentary' => "They saw it. They read it. They said... absolutely nothing. Is it a glitch or are you just being ghosted? 💀👻",
        'stamp' => 'Seen'
    ];
}

if ($status == "accepted") {
    $config = [
        'theme' => '#2ecc71',
        'bg' => 'linear-gradient(135deg, #e8f5e9, #a5d6a7)',
        'emoji' => '💅',
        'live_emojis' => ['🥂', '🍕', '💍', '💅', '💖'],
        'commentary' => "TARGET ACQUIRED. You're officially off the market. Please stop sweating now, it's embarrassing. 🥂💅",
        'stamp' => 'Success'
    ];
}

if ($status == "rejected") {
    $config = [
        'theme' => '#e74c3c',
        'bg' => 'linear-gradient(135deg, #fdedec, #fadbd8)',
        'emoji' => '💔',
        'live_emojis' => ['💔', '🥀', '🌧️', '🎈'],
        'commentary' => "Mission Failed. We'll get 'em next time (probably not, though). Better luck in the next life! 💔🥀",
        'stamp' => 'Denied'
    ];
}

if ($status == "rejected_by_genius") {
    $config = [
        'theme' => '#555',
        'bg' => 'linear-gradient(135deg, #1a1a1a, #000000)',
        'emoji' => '🧠',
        'live_emojis' => ['💀', '🧠', '🧊', '📉'],
        'commentary' => "DEFEAT. They solved the math just to kill the vibe. You've been rejected by a cold, calculating robot. 🧠💀",
        'stamp' => 'Void'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="icon.ico">
    <title>Yesly Intel</title>
    <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --theme: <?= $config['theme'] ?>; --bg: <?= $config['bg'] ?>; }
        body, html { margin: 0; padding: 0; height: 100%; width: 100%; background: var(--bg); font-family: 'Quicksand', sans-serif; display: flex; justify-content: center; align-items: center; overflow: hidden; transition: all 1s ease; }
        #emoji-bg { position: fixed; inset: 0; z-index: 1; pointer-events: none; }
        .floating-emoji { position: fixed; font-size: 24px; opacity: 0.3; pointer-events: none; z-index: 1; animation-name: floatUp; animation-timing-function: linear; animation-iteration-count: infinite; }
        @keyframes floatUp { 0% { transform: translateY(100vh) rotate(0deg); opacity: 0; } 10% { opacity: 0.3; } 100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; } }
        .notebook { background: #fff; width: 90%; max-width: 400px; padding: 30px; border-radius: 15px; border: 2px solid #333; box-shadow: 10px 10px 0px var(--theme); position: relative; z-index: 10; }
        .notebook::before { content: ''; position: absolute; top: 0; left: 40px; width: 1px; height: 100%; background: rgba(255, 0, 0, 0.1); }
        h2 { font-family: 'Handlee', cursive; color: var(--theme); text-align: center; margin-top: 0; }
        .stamp { position: absolute; right: 15px; top: 15px; border: 3px solid var(--theme); color: var(--theme); padding: 5px 10px; font-weight: bold; transform: rotate(15deg); text-transform: uppercase; font-size: 0.8rem; }
        .pulse-box { background: rgba(0,0,0,0.03); border-left: 4px solid var(--theme); padding: 15px; margin: 20px 0; font-style: italic; }
        .update-btn { background: #333; color: #fff; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-family: inherit; font-weight: bold; margin-top: 10px; }
        .update-btn:hover { background: var(--theme); color: #000; }
        .big-status-emoji { font-size: 60px; display: block; text-align: center; margin-bottom: 10px; }
        
        /* Share Section Styles */
        .share-section { margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 15px; }
        .share-label { font-size: 11px; font-weight: bold; color: #888; text-transform: uppercase; display: block; margin-bottom: 10px; text-align: center; }
        .share-grid { display: flex; gap: 8px; }
        .share-btn { flex: 1; border: none; padding: 10px; border-radius: 8px; color: white; text-decoration: none; font-size: 12px; font-weight: bold; text-align: center; display: flex; align-items: center; justify-content: center; gap: 5px; }
    </style>
</head>
<body>

    <div id="emoji-bg"></div>

    <div class="notebook">
        <div class="stamp"><?= $config['stamp'] ?></div>
        <span class="big-status-emoji"><?= $config['emoji'] ?></span>
        <h2>Intelligence Report</h2>
        
        <div style="margin-bottom: 15px;">
            <label style="font-size: 0.7rem; color: #999; text-transform: uppercase;">Target:</label>
            <div style="font-weight: 600;"><?= htmlspecialchars($p["receiver_name"]) ?></div>
        </div>

        <div class="pulse-box">
            "<?= $config['commentary'] ?>"
        </div>

        <?php if ($status === "accepted"): ?>
        <div class="share-section">
            <span class="share-label">Tell the world! 📣</span>
            <div class="share-grid">
                <a href="https://api.whatsapp.com/send?text=<?= $encodedText ?>" target="_blank" class="share-btn" style="background: #25D366;">
                    WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareLink) ?>" target="_blank" class="share-btn" style="background: #1877F2;">
                    Facebook
                </a>
            </div>
        </div>
        <?php endif; ?>

        <button class="update-btn" onclick="location.reload()">UPDATE INTEL</button>
    </div>

    <script>
        const bg = document.getElementById('emoji-bg');
        const emojis = <?= json_encode($config['live_emojis']) ?>;
        function createEmoji() {
            const el = document.createElement('div');
            el.className = 'floating-emoji';
            el.innerHTML = emojis[Math.floor(Math.random() * emojis.length)];
            el.style.left = Math.random() * 100 + 'vw';
            el.style.fontSize = (Math.random() * 24 + 16) + 'px';
            el.style.animationDuration = (Math.random() * 5 + 5) + 's';
            bg.appendChild(el);
            setTimeout(() => el.remove(), parseFloat(el.style.animationDuration) * 1000);
        }
        setInterval(createEmoji, 300);
    </script>
</body>
</html>