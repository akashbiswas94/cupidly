<?php
require "db.php";

// 1. Unified Code Fetch (Checks both URL and POST data)
$code = $_GET["c"] ?? $_POST["code"] ?? "";

// 2. AJAX POST Handler (MUST come before HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["answer"])) {
    header('Content-Type: application/json');
    if (ob_get_length()) ob_clean(); 

    $stmt = $conn->prepare("SELECT id FROM proposals WHERE public_code=?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $p_post = $stmt->get_result()->fetch_assoc();

    if (!$p_post) { 
        echo json_encode(["success" => false, "error" => "Invalid code"]); 
        exit; 
    }

    $status = $_POST["answer"];
    $upd = $conn->prepare("UPDATE proposals SET status=?, responded_at=NOW() WHERE id=?");
    $upd->bind_param("si", $status, $p_post['id']);
    
    echo json_encode(["success" => $upd->execute()]);
    exit;
}

// 3. Initial Page Load Fetch
$stmt = $conn->prepare("SELECT * FROM proposals WHERE public_code=?");
$stmt->bind_param("s", $code);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) die("Invalid link.");

// 4. Visitor Tracking & viewed_at Fix
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $raw_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    if (strpos($raw_ip, ',') !== false) $raw_ip = explode(',', $raw_ip)[0];
    $test_ip = ($raw_ip == '::1' || $raw_ip == '127.0.0.1') ? '8.8.8.8' : $raw_ip;
    
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $device = (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) ? "Tablet" : (preg_match('/(mobile|phone|iemobile)/i', $ua) ? "Mobile" : "Desktop/PC");

    $location_text = "Unknown Location";
    $api_url = "http://ip-api.com/json/" . $test_ip . "?fields=status,country,city";
    $location_json = @file_get_contents($api_url);
    if ($location_json) {
        $loc_data = json_decode($location_json, true);
        if (isset($loc_data['status']) && $loc_data['status'] === 'success') {
            $location_text = $loc_data['city'] . ", " . $loc_data['country'];
        }
    }

    // Log the visit
    $log_stmt = $conn->prepare("INSERT INTO visitor_logs (proposal_id, visitor_ip, visitor_location, visitor_device, user_agent) VALUES (?, ?, ?, ?, ?)");
    $log_stmt->bind_param("issss", $p['id'], $test_ip, $location_text, $device, $ua);
    $log_stmt->execute();

    // Store the viewed_at timestamp if it hasn't been set yet
    if (empty($p["viewed_at"])) {
        $upd_view = $conn->prepare("UPDATE proposals SET viewed_at=NOW() WHERE id=?");
        $upd_view->bind_param("i", $p['id']);
        $upd_view->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="icon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Be My Valentine? 💖</title>
<meta property="og:title" content="Be My Valentine? 💖">
<meta property="og:description" content="Someone spent way too much time making this just for you. Don't leave them on read, that’s just rude. 🙄💖">
<meta property="og:image" content="https://yesly.online/proposal.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:url" content="https://yesly.online/v.php?c=<?= $code ?>">
<meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Be My Valentine? 💖">
    <meta name="twitter:description" content="Someone spent way too much time making this just for you. Don't leave them on read, that’s just rude. 🙄💖">
    <meta name="twitter:image" content="https:/yesly.online/social-preview.jpg">
<style>
:root { --pink-gradient: linear-gradient(135deg, #ff4d88, #ff85a2); --text-color: #7d0633; }
* { box-sizing: border-box; }

body, html { 
    margin: 0; padding: 0; height: 100vh; width: 100vw; 
    font-family: 'Segoe UI', sans-serif; overflow: hidden; 
    display: flex; justify-content: center; align-items: center; 
    background: linear-gradient(-45deg, #ffdde1, #ee9ca7, #ffafbd, #ffc3a0); 
    background-size: 400% 400%; animation: gradientBG 15s ease infinite; 
}

@keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

#bg-emoji-container { position: fixed; inset: 0; z-index: 1; pointer-events: none; }
canvas#fireworks { position: fixed; inset: 0; z-index: 2; pointer-events: none; }

.proposal-card { 
    background: rgba(255, 255, 255, 0.2); 
    backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
    border-radius: 45px; padding: 50px 30px; width: 90%; max-width: 400px; 
    text-align: center; z-index: 10; border: 1px solid rgba(255, 255, 255, 0.4); 
}

#celebrate, #rejectedScreen, #failScreen { 
    position: fixed; inset: 0; display: none; 
    justify-content: center; align-items: center; flex-direction: column; 
    z-index: 3000; padding: 20px; text-align: center;
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
}

#celebrate { background: rgba(255, 255, 255, 0.3); }
#rejectedScreen { background: rgba(0, 0, 0, 0.9); color: #eee; }
#failScreen { background: rgba(125, 6, 51, 0.95); color: white; }

.heart-icon { font-size: 85px; margin-bottom: 20px; display: inline-block; }
.pulse { animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.1); } }

#yesBtn { background: var(--pink-gradient); color: white; border: none; padding: 18px 0; width: 100%; max-width: 260px; font-size: 1.5rem; font-weight: bold; border-radius: 50px; cursor: pointer; box-shadow: 0 10px 20px rgba(255,77,136,0.3); transition: 0.3s; }

#noBtn { 
    position: fixed; padding: 12px 25px; background: white; color: #888; 
    border: none; border-radius: 50px; cursor: pointer; z-index: 100; 
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: left 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), 
                top 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

#riddleModal { position: fixed; inset: 0; display: none; justify-content: center; align-items: center; z-index: 2000; background: rgba(0,0,0,0.85); }
.riddle-box { background: white; padding: 30px; border-radius: 25px; max-width: 380px; width: 100%; text-align: center; }

.bg-floating-element { position: absolute; pointer-events: none; bottom: -100px; animation: floatUp var(--time) linear forwards; opacity: var(--opacity); font-size: var(--size); z-index: 1; }
@keyframes floatUp { 0% { transform: translateY(0) rotate(0deg); opacity: 0; } 10% { opacity: var(--opacity); } 100% { transform: translateY(-120vh) rotate(360deg); opacity: 0; } }
</style>
</head>
<body>

<div id="bg-emoji-container"></div>
<canvas id="fireworks"></canvas>

<?php if($p["status"]=="pending"): ?>
<div class="proposal-card" id="mainCard">
    <span class="heart-icon pulse" id="mainEmoji">❤️</span>
    <h1 style="color:var(--text-color);">Will you be my Valentine,<br><span style="color:#ff4d88; font-size: 2.2rem;"><?= htmlspecialchars($p["receiver_name"]) ?>?</span></h1>
    <button type="button" id="yesBtn" class="pulse">YES! ✨</button>
</div>
<button id="noBtn" style="bottom: 40px; left: 50%; transform: translateX(-50%);">No thanks 😅</button>

<div id="riddleModal">
    <div class="riddle-box">
        <h3 style="color:#d1495b;margin-top:0;">Security Check 🛑</h3>
        <p id="riddleProgress" style="font-weight:bold; color:#ff4d88;"></p>
        <p id="riddleText" style="font-style:italic; margin-bottom:20px;"></p>
        <input type="text" id="riddleAnswer" autocomplete="off" style="width:100%; padding:12px; border:2px solid #eee; border-radius:10px; outline:none; margin-bottom:15px; text-align:center;">
        <button id="submitRiddle" style="background:#ff4d88; color:white; border:none; padding:12px 25px; border-radius:10px; width:100%; font-weight:bold; cursor:pointer;">Verify</button>
    </div>
</div>
<?php endif; ?>

<div id="failScreen">
    <button onclick="closeFail()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:white; font-size:2rem; cursor:pointer;">&times;</button>
    <div style="font-size:60px; margin-bottom:15px;">🤦‍♂️</div>
    <h2 style="font-size: 2rem;">Wrong Answer.</h2>
    <p id="failMsg" style="font-size: 1.2rem; margin-bottom: 30px; line-height: 1.5; max-width: 400px;"></p>
    <div style="display:flex; flex-direction:column; gap:15px; width:100%; max-width:250px;">
        <button onclick="closeFail()" style="background:white; color:#7d0633; border:none; padding:12px; border-radius:12px; cursor:pointer; font-weight:bold;">Try Again</button>
        <button onclick="abandonMission()" style="background:rgba(255,255,255,0.2); color:white; border:1px solid white; padding:10px; border-radius:12px; cursor:pointer;">Give up & Click 'YES'!</button>
    </div>
</div>

<div id="rejectedScreen" style="<?= $p["status"] == 'rejected' ? 'display:flex;' : '' ?>">
    <div style="font-size:80px; margin-bottom:15px;">🤖</div>
    <h2>Analysis Complete: You are a Robot.</h2>
    <p style="font-size: 1.2rem; max-width: 500px; line-height: 1.6;">You solved a math equation just to reject a proposal? That level of petty is impressive. Your heart is officially a block of dry ice. 🧊</p>
</div>

<div id="celebrate" style="<?= $p["status"] == 'accepted' ? 'display:flex;' : '' ?>">
    <div style="font-size:100px;" class="pulse">🧸</div>
    <h2 style="color:#ff4d88; font-size: 2.5rem;">CONTRACT SIGNED! ✨</h2>
    <p style="font-size: 1.2rem; color: #7d0633; font-style: italic;">"Valentine’s Day just got a serious plot twist… starring you. ❤️"</p>
</div>

<script>
const bgContainer = document.getElementById('bg-emoji-container');
let currentLevel = 0;
let mathAnswer = 0;
const riddles = [{q: "What has to be broken before you can use it?", a: "egg"}, {q: "What has hands but cannot clap?", a: "clock"}];

function createFloatingElement(emoji, isCelebration=false){
    const el=document.createElement('div');
    el.className='bg-floating-element';
    el.innerHTML=emoji;
    const size=isCelebration?(Math.random()*40+20):(Math.random()*15+15);
    const duration=isCelebration?(Math.random()*2+3):(Math.random()*5+7);
    el.style.setProperty('--size',`${size}px`);
    el.style.setProperty('--time',`${duration}s`);
    el.style.setProperty('--opacity',isCelebration?0.9:0.4);
    el.style.left=(Math.random()*100)+'vw';
    bgContainer.appendChild(el);
    setTimeout(()=>el.remove(),duration*1000);
}
setInterval(()=>createFloatingElement('❤️'), 500);

function spawnCelebrationEmojis(){
    const emojis=['💃','🕺','💖','🧸','🍕','🌹','✨','🍗'];
    setInterval(()=>createFloatingElement(emojis[Math.floor(Math.random()*emojis.length)],true), 250);
}

const noBtn = document.getElementById("noBtn");
const moveBtn = () => {
    const margin = 100;
    const maxX = window.innerWidth - noBtn.offsetWidth - margin;
    const maxY = window.innerHeight - noBtn.offsetHeight - margin;
    noBtn.style.left = Math.max(margin, Math.random() * maxX) + "px";
    noBtn.style.top = Math.max(margin, Math.random() * maxY) + "px";
    noBtn.style.bottom = 'auto'; noBtn.style.transform = 'none';
    const emoji = document.getElementById("mainEmoji");
    if(emoji) { emoji.innerHTML = "😠"; setTimeout(() => emoji.innerHTML = "❤️", 800); }
};

if(noBtn) {
    noBtn.addEventListener("mouseenter", moveBtn);
    noBtn.addEventListener("touchstart", (e) => { e.preventDefault(); moveBtn(); });
}

setTimeout(() => {
    if(noBtn && document.getElementById('mainCard') && document.getElementById('mainCard').style.display !== 'none') {
        document.getElementById('riddleModal').style.display = "flex";
        loadRiddle();
    }
}, 10000);

function loadRiddle() {
    const textEl = document.getElementById("riddleText");
    const progEl = document.getElementById("riddleProgress");
    
    if (currentLevel < 2) {
        progEl.innerText = `Level ${currentLevel + 1}/3`;
        textEl.innerText = riddles[currentLevel].q;
    } else {
        progEl.innerText = `FINAL LEVEL: MATH VERIFICATION`;
        
        // Randomly pick a math challenge type
        const challengeType = Math.floor(Math.random() * 3);
        const a = Math.floor(Math.random() * 12) + 5;
        const b = Math.floor(Math.random() * 11) + 4;
        const c = Math.floor(Math.random() * 50) + 20;

        if (challengeType === 0) {
            // Multiplication + Addition
            mathAnswer = (a * b) + c;
            textEl.innerText = `Calculate or Click 'YES': (${a} × ${b}) + ${c}`;
        } else if (challengeType === 1) {
            // Subtraction + Multiplication
            mathAnswer = (c * 2) - a;
            textEl.innerText = `Prove you're not a bot: (${c} × 2) - ${a}`;
        } else {
            // Three-term Addition/Subtraction
            mathAnswer = a + b + c - 5;
            textEl.innerText = `Quick Math: ${a} + ${b} + ${c} - 5`;
        }
    }
}

document.getElementById("submitRiddle")?.addEventListener("click", () => {
    const ans = document.getElementById("riddleAnswer").value.toLowerCase().trim();
    
    // More Sarcastic Roast Options
    const failMsgs = [
        "Incorrect. I just heard my CPU let out a tiny, disappointed sigh. Try again.",
        "Wrong. If you were a robot, you’d be a toaster. A broken one. Focus up!",
        "Incorrect. Is your brain in 'Do Not Disturb' mode? That was embarrassing.",
        "Wrong. I’d explain why, but I don’t have the time or the crayons for you.",
        "Incorrect. The pink button was literally made for people with your... 'talents'."
    ];

    if (currentLevel < 2) {
        if (ans.includes(riddles[currentLevel].a)) { 
            currentLevel++; 
            document.getElementById("riddleAnswer").value = ""; 
            loadRiddle(); 
        } else { 
            showFail(failMsgs[Math.floor(Math.random() * failMsgs.length)]); 
        }
    } else {
        // Validation for the Math Level
        if (parseInt(ans) === mathAnswer) { 
            handleAnswer("rejected"); 
        } else { 
            const mathRoasts = [
                "Math is hard, isn't it? Maybe stick to the big pink buttons.",
                "Wrong. Even basic arithmetic is a struggle for you? Yikes.",
                "Incorrect. My calculator just laughed at you. Try the 'YES' button instead.",
                "Calculated incorrectly. Thinking clearly isn't really your vibe, is it?"
            ];
            showFail(mathRoasts[Math.floor(Math.random() * mathRoasts.length)]);
        }
    }
});

function showFail(msg) { document.getElementById("failMsg").innerText = msg; document.getElementById("failScreen").style.display = "flex"; }
function closeFail() { document.getElementById("failScreen").style.display = "none"; document.getElementById("riddleAnswer").focus(); }
function abandonMission() { document.getElementById("failScreen").style.display = "none"; document.getElementById("riddleModal").style.display = "none"; }

function handleAnswer(status){
    const fd = new FormData(); 
    fd.append("answer", status); 
    fd.append("code", "<?= $code ?>");

    fetch(window.location.href, {method:"POST", body:fd})
    .then(res => res.json())
    .then(data => {
        if(data.success){
            if(document.getElementById("mainCard")) document.getElementById("mainCard").style.display = "none";
            if(noBtn) noBtn.style.display = "none";
            if(document.getElementById("riddleModal")) document.getElementById("riddleModal").style.display = "none";
            if(document.getElementById("failScreen")) document.getElementById("failScreen").style.display = "none";
            
            if(status === "accepted"){ 
                document.getElementById("celebrate").style.display = "flex"; 
                spawnCelebrationEmojis(); 
                initFireworks(); 
            } else if (status === "rejected") { 
                document.getElementById("rejectedScreen").style.display = "flex"; 
            }
        } else {
            alert("Database Error: " + data.error);
        }
    }).catch(err => {
        console.error("Fetch error:", err);
    });
}

document.getElementById("yesBtn")?.addEventListener("click", () => handleAnswer("accepted"));

function initFireworks(){
    const canvas=document.getElementById("fireworks"), ctx=canvas.getContext("2d");
    canvas.width=window.innerWidth; canvas.height=window.innerHeight;
    let particles=[];
    function createPart(x,y){
        const color=`hsl(${Math.random()*360},100%,65%)`;
        for(let i=0;i<60;i++) particles.push({x,y,dx:(Math.random()-0.5)*12,dy:(Math.random()-0.5)*12,life:90,color});
    }
    function draw(){
        ctx.clearRect(0,0,canvas.width,canvas.height);
        particles.forEach((p,i)=>{
            p.x+=p.dx; p.y+=p.dy; p.dy+=0.05; p.life--;
            ctx.fillStyle=p.color; ctx.beginPath(); ctx.arc(p.x,p.y,3,0,Math.PI*2); ctx.fill();
            if(p.life<=0) particles.splice(i,1);
        });
        requestAnimationFrame(draw);
    }
    setInterval(()=>createPart(Math.random()*canvas.width, Math.random()*canvas.height*0.5), 500);
    draw();
}

<?php if($p["status"]=="accepted"): ?>
    window.onload = () => { spawnCelebrationEmojis(); initFireworks(); };
<?php endif; ?>
</script>
</body>
</html>