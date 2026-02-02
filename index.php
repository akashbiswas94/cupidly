<?php
require "db.php";

// Helper function for unique codes
function generateCode($len) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $len);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sender   = trim($_POST["sender"]);
    $receiver = trim($_POST["receiver"]);

    $public  = generateCode(8);
    $private = generateCode(12);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO proposals (sender_name, receiver_name, public_code, private_code) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $sender, $receiver, $public, $private);
    $stmt->execute();

    // Generate Dynamic URLs
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host     = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if ($basePath === '.') $basePath = '';
    $baseUrl  = $protocol . "://" . $host . $basePath;

    $valentine = $baseUrl . "/v.php?c=" . urlencode($public);
    $result    = $baseUrl . "/result.php?c=" . urlencode($private);
    $emailSubject = "A secret message for you 💖";
    $emailBody = "Hey 😊\n\nI’ve been meaning to ask you something — and I thought I’d do it in a sweet way. Click here: " . $valentine;

    // We use rawurlencode to ensure spaces and emojis are converted to safe % characters
    $encodedSubject = rawurlencode($emailSubject);
    $encodedEmail = rawurlencode($emailBody);

    // Pre-formatted message for sharing
    $shareText = "Hey 😊 I’ve been meaning to ask you something — and I thought I’d do it in a sweet way. Click here: " . $valentine;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Yesly | Create Your Interactive Valentine's Proposal</title>
    <meta property="og:title" content="Be My Valentine? 💖">
    <meta property="og:description" content="Someone spent way too much time making this just for you. Don't leave them on read, that’s just rude. 🙄💖">
    <meta property="og:image" content="https://yesly.online/proposal.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="https://yesly.online/">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Be My Valentine? 💖">
    <meta name="twitter:description" content="Someone spent way too much time making this just for you. Don't leave them on read, that’s just rude. 🙄💖">
    <meta name="twitter:image" content="https://yesly.online/proposal.png">

    <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Quicksand:wght@400;600&display=swap" rel="stylesheet"><style>
        :root {
            --primary: #ff4d88;
            --primary-hover: #e63977;
            --secondary: #ff85a2;
            --bg-gradient: linear-gradient(135deg, #fce4ec 0%, #ffd1dc 100%);
            --glass: rgba(255, 255, 255, 0.85);
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            margin: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            overflow-x: hidden;
        }

        /* Floating Hearts Background */
        .bg-hearts { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; pointer-events: none; }
        .bg-hearts span {
            position: absolute; display: block; background: rgba(255, 77, 136, 0.15);
            bottom: -100px; border-radius: 50%; animation: float 20s linear infinite;
        }
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translateY(-120vh) rotate(360deg); opacity: 0; }
        }

        .container {
            width: 90%; max-width: 440px;
            background: var(--glass);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 30px; padding: 40px 25px;
            box-shadow: 0 20px 40px rgba(255, 77, 136, 0.15);
            text-align: center;
        }

        h2 { color: #c2185b; font-size: 26px; margin-bottom: 10px; font-weight: 800; }
        p.subtitle { color: #7d636a; font-size: 14px; margin-bottom: 25px; line-height: 1.4; }

        .input-group { text-align: left; margin-bottom: 18px; }
        label { font-size: 11px; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px; }
        input {
            width: 100%; padding: 14px; border-radius: 12px; border: 2px solid #ffdae0;
            background: white; font-size: 16px; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(255, 77, 136, 0.1); }

        .main-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border: none; padding: 16px; border-radius: 15px;
            width: 100%; font-size: 18px; font-weight: bold; cursor: pointer;
            box-shadow: 0 8px 15px rgba(255, 77, 136, 0.2); transition: 0.3s;
        }
        .main-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 20px rgba(255, 77, 136, 0.3); }

        /* Result Cards */
        .card-result {
            background: white; border-radius: 20px; padding: 20px;
            border: 1px solid #ffdae0; text-align: left; margin-bottom: 20px;
        }
        .share-text-box {
            background: #fff5f7; padding: 15px; border-radius: 12px;
            font-size: 14px; color: #444; line-height: 1.5; border: 1px dashed var(--primary);
        }
        
        .btn-row { display: flex; gap: 10px; margin-top: 15px; }
        .action-btn {
            flex: 1; border: none; padding: 12px; border-radius: 10px;
            font-weight: bold; cursor: pointer; font-size: 13px; color: white;
            display: flex; align-items: center; justify-content: center; gap: 5px;
            transition: 0.2s;
        }
        .copy-btn { background: var(--primary); }
        .wa-btn { background: #25D366; text-decoration: none; }

        .tracker-info {
            background: #e8f5e9; border: 1px solid #a5d6a7;
            padding: 15px; border-radius: 20px; color: #2e7d32; text-align: left;
        }
        .url-display {
            background: rgba(255, 255, 255, 0.6); padding: 10px; 
            border-radius: 8px; font-size: 12px; margin-top: 8px;
            display: flex; justify-content: space-between; align-items: center;
        }

        .btn-reset {
            background: none; border: 1px solid #ccc; color: #888;
            padding: 10px; border-radius: 12px; width: 100%; cursor: pointer; margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="bg-hearts" id="hearts-container"></div>

<div class="container">
    <?php if(!isset($valentine)): ?>
        <h2>Create Magic 💌</h2>
        <p class="subtitle">Enter your names to generate a magical, interactive proposal link.</p>
        <form method="post">
            <div class="input-group">
                <label>Your Name</label>
                <input name="sender" placeholder="Romeo" required>
            </div>
            <div class="input-group">
                <label>Their Name</label>
                <input name="receiver" placeholder="Juliet" required>
            </div>
            <button type="submit" class="main-btn">Generate Magic ✨</button>
        </form>
    <?php else: ?>
        <h2>Ready to Share! 💖</h2>
        <p class="subtitle">Copy the message below or share it directly to your favorite apps.</p>
        
        <div class="card-result">
            <label>🎁 THE PROPOSAL MESSAGE</label>
            <div class="share-text-box" id="fullMessage">
                Hey 😊 I’ve been meaning to ask you something — and I thought I’d do it in a sweet way. Click here: <a href="<?= $valentine ?>" target="_blank" rel="noopener" style="color:var(--primary); font-weight:bold; text-decoration:none;">Open My Message 💌</a>
            </div>
            
            <div class="btn-row">
                <button class="action-btn copy-btn" onclick="copyFullMessage(this)">
                    Copy Text 📋
                </button>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($shareText) ?>" target="_blank" class="action-btn wa-btn">
                    WhatsApp 💬
                </a>
            </div>
            <a href="mailto:?subject=<?= $encodedSubject ?>&body=<?= $encodedEmail ?>" 
                target="_blank"
                rel="noopener"
                style="display:block; text-align:center; font-size:12px; margin-top:12px; color:#999; text-decoration:none; cursor:pointer;">
                Or send via Email ✉️
                </a>
        </div>

        <div class="tracker-info">
            <label style="color: #2e7d32; display: block; margin-bottom: 8px;">🔐 PRIVATE TRACKER (ONLY YOU)</label>
            <p style="font-size: 12px; margin: 0 0 12px 0;">Use this button to check if they've said Yes!</p>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="<?= $result ?>" target="_blank" id="tLink" 
                style="flex: 1; background: #2e7d32; color: white; text-decoration: none; text-align: center; padding: 12px; border-radius: 12px; font-weight: bold; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 10px rgba(46, 125, 50, 0.2);">
                View Live Intel 
                </a>
                
                <button onclick="copyTracker(this, '<?= $result ?>')" 
                        style="background: white; border: 1px solid #2e7d32; color: #2e7d32; padding: 12px; border-radius: 12px; cursor: pointer; font-weight: bold; transition: 0.2s;">
                Copy Link
                </button>
            </div>
            <small style="display:block; margin-top:10px; font-size: 11px; opacity: 0.8;">⚠️ Do not share this with the target.</small>
        </div>

        <button onclick="window.location.href='index.php'" class="btn-reset">Create Another One</button>
    <?php endif; ?>
</div>

<script>
    // 1. Generate Floating Hearts
    const hContainer = document.getElementById('hearts-container');
    for (let i = 0; i < 15; i++) {
        const span = document.createElement('span');
        const size = Math.random() * 50 + 15 + 'px';
        span.style.width = size;
        span.style.height = size;
        span.style.left = Math.random() * 100 + '%';
        span.style.animationDelay = Math.random() * 15 + 's';
        span.style.animationDuration = Math.random() * 10 + 10 + 's';
        hContainer.appendChild(span);
    }

    // 2. FIXED: Copy Full Message with functional URL
    function copyFullMessage(btn) {
        // Get the introductory text
        const box = document.getElementById('fullMessage');
        const introText = "Hey 😊 I’ve been meaning to ask you something — and I thought I’d do it in a sweet way. Click here: ";
        
        // Explicitly get the URL from the <a> tag's href
        const url = box.querySelector('a').href;
        
        // Combine them
        const finalContent = introText + url + " 💌";
        
        navigator.clipboard.writeText(finalContent).then(() => {
            const oldText = btn.innerHTML;
            btn.innerHTML = "Copied! ✅";
            btn.style.background = "#4CAF50";
            setTimeout(() => {
                btn.innerHTML = oldText;
                btn.style.background = "";
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    // 3. Copy Tracker URL only
    function copyTracker(btn, url) {
        navigator.clipboard.writeText(url).then(() => {
            const oldText = btn.innerText;
            btn.innerText = "Copied!";
            btn.style.background = "#1b5e20";
            setTimeout(() => {
                btn.innerText = oldText;
                btn.style.background = "";
            }, 2000);
        });
    }
</script>
</body>
</html>