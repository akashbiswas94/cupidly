<?php
require "db.php";
$code = $_GET["c"] ?? "";

// 1. Fetch the Proposal
$stmt = $conn->prepare("SELECT * FROM proposals WHERE public_code=?");
$stmt->bind_param("s", $code);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();

if (!$p) die("Invalid link");

// 2. Capture Visitor Information
$proposal_id = $p['id'];

// Get IP Address and Mask it
$raw_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

if (strpos($raw_ip, ',') !== false) $raw_ip = explode(',', $raw_ip)[0];

// Masking logic: transforms 123.456.78.90 into 123.456.78.XXX
$ip_parts = explode('.', $raw_ip);
if(count($ip_parts) === 4) {
    $masked_ip = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2] . '.XXX';
} else {
    $masked_ip = "Hidden IP"; 
}

// Get Device Type
$ua = $_SERVER['HTTP_USER_AGENT'];
$device = "Desktop/PC";
if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
    $device = "Tablet";
} else if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $ua)) {
    $device = "Mobile";
}

// Get Detailed Location (City + Country) via API
$location_text = "Unknown Location";
$api_url = "http://ip-api.com/json/" . $raw_ip . "?fields=status,country,city";
$location_json = @file_get_contents($api_url);
if ($location_json) {
    $loc_data = json_decode($location_json, true);
    if ($loc_data && $loc_data['status'] === 'success') {
        $location_text = $loc_data['city'] . ", " . $loc_data['country'];
    }
}

// 3. Save to Visitor Logs
$log_stmt = $conn->prepare("INSERT INTO visitor_logs (proposal_id, visitor_ip, visitor_location, visitor_device, user_agent) VALUES (?, ?, ?, ?, ?)");
$log_stmt->bind_param("issss", $proposal_id, $masked_ip, $location_text, $device, $ua);
$log_stmt->execute();

// 4. Update viewed_at for first-time view
if (!$p["viewed_at"]) {
    $conn->query("UPDATE proposals SET viewed_at=NOW() WHERE id={$p['id']}");
}

// 5. Handle Answer Submission
if (isset($_POST["answer"])) {
    $status = $_POST["answer"];
    $upd = $conn->prepare("UPDATE proposals SET status=?, responded_at=NOW() WHERE id=?");
    $upd->bind_param("si", $status, $p["id"]);
    $upd->execute();
    $p["status"] = $status;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Be My Valentine? 💖</title>
    <style>
        /* YOUR EXISTING CSS START */
        :root { --pink-gradient: linear-gradient(135deg, #ff4d88, #ff85a2); --text-color: #7d0633; }
        * { box-sizing: border-box; }
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Segoe UI', Roboto, sans-serif; overflow: hidden; display: flex; justify-content: center; align-items: center; background: linear-gradient(-45deg, #ffdde1, #ee9ca7, #ffafbd, #ffc3a0); background-size: 400% 400%; animation: gradientBG 20s ease infinite; }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        #particles-js { position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 1; pointer-events: none; }
        .proposal-card { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 45px; padding: 60px 35px; width: 88%; max-width: 400px; text-align: center; z-index: 10; position: relative; border: 1px solid rgba(255, 255, 255, 0.3); transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        .heart-icon { font-size: 85px; margin-bottom: 25px; display: inline-block; transition: 0.3s; }
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.15); text-shadow: 0 0 15px rgba(255,77,136,0.6); } }
        h1 { color: var(--text-color); font-size: 1.5rem; margin-bottom: 40px; font-weight: 700; letter-spacing: -0.5px; }
        h1 span { color: #ff4d88; display: block; font-size: 2.3rem; margin-top: 10px; }
        #yesBtn { background: var(--pink-gradient); color: white; border: none; padding: 18px 0; width: 100%; max-width: 260px; font-size: 1.5rem; font-weight: bold; border-radius: 50px; cursor: pointer; box-shadow: 0 12px 24px rgba(255,77,136,0.35); transition: 0.3s ease; }
        #yesBtn:hover { transform: translateY(-4px) scale(1.05); box-shadow: 0 18px 35px rgba(255,77,136,0.5); }
        #noBtn { position: fixed; padding: 12px 28px; background: rgba(255, 255, 255, 0.9); color: #888; border: none; border-radius: 50px; font-size: 0.95rem; cursor: pointer; z-index: 100; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(0,0,0,0.06); }
        #celebrate { position: fixed; inset: 0; display: none; justify-content: center; align-items: center; flex-direction: column; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); z-index: 200; animation: fadeIn 1s forwards; text-align: center; padding: 20px; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        canvas { position: fixed; inset: 0; z-index: 150; pointer-events: none; }
        /* YOUR EXISTING CSS END */
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?php if($p["status"] == "pending"): ?>
    <div class="proposal-card">
        <span class="heart-icon pulse" id="mainEmoji">❤️</span>
        <h1>Will you be my Valentine, <br><span><?= htmlspecialchars($p["receiver_name"]) ?>?</span></h1>
        <form method="post" id="valForm">
            <input type="hidden" name="answer" value="accepted">
            <button type="button" id="yesBtn">YES! ✨</button>
        </form>
    </div>
    <button id="noBtn">No thanks 😅</button>
    <?php endif; ?>
    <div id="celebrate">
        <div style="font-size: 100px; animation: pulse 1s infinite;">💖</div>
        <h2 style="font-size: clamp(2rem, 8vw, 3.5rem); color: #ff4d88; margin-top: 20px;">It's a Date! ✨</h2>
        <p style="font-size: 1.4rem; color: #7d0633; font-style: italic;">"I knew you couldn't resist. 😉 <br> Can't wait for our beautiful beginning!"</p>
    </div>
    <canvas id="fireworks"></canvas>
    <script>
        /* YOUR EXISTING SCRIPT START */
        const particleContainer = document.getElementById('particles-js');
        function createHeart() {
            const heart = document.createElement('div');
            heart.innerHTML = Math.random()<0.3?'💖':'❤️';
            heart.style.position = 'absolute';
            heart.style.left = Math.random()*100 + 'vw';
            heart.style.top = '110vh';
            heart.style.fontSize = (Math.random()*15+10) + 'px';
            heart.style.opacity = Math.random()*0.3+0.3;
            heart.style.transition = `transform ${Math.random()*5+7}s linear, opacity 0.5s`;
            particleContainer.appendChild(heart);
            setTimeout(()=>{ heart.style.transform = `translateY(-120vh) rotate(${Math.random()*360}deg)`; }, 100);
            setTimeout(()=>heart.remove(), 12000);
        }
        setInterval(createHeart, 400);

        const noBtn = document.getElementById("noBtn");
        const mainEmoji = document.getElementById("mainEmoji");
        if(noBtn && mainEmoji){
            const moveBtn = () => {
                const x = Math.random()*(window.innerWidth-noBtn.offsetWidth-60);
                const y = Math.random()*(window.innerHeight-noBtn.offsetHeight-60);
                noBtn.style.left = `${x}px`;
                noBtn.style.top = `${y}px`;
                mainEmoji.innerHTML="😠";
                setTimeout(()=>{ mainEmoji.innerHTML="❤️"; }, 800);
            };
            noBtn.addEventListener("mouseenter", moveBtn);
            noBtn.addEventListener("touchstart", e=>{ e.preventDefault(); moveBtn(); });
        }

        const yesBtn = document.getElementById("yesBtn");
        const celebrateDiv = document.getElementById("celebrate");
        <?php if($p["status"] == "accepted"): ?>
            celebrateDiv.style.display="flex";
            initFireworks();
        <?php endif; ?>

        if(yesBtn){
            yesBtn.addEventListener("click", e=>{
                document.querySelector(".proposal-card").style.transform="scale(0) rotate(10deg)";
                setTimeout(() => {
                    celebrateDiv.style.display="flex";
                    initFireworks();
                    setTimeout(() => document.getElementById("valForm").submit(), 2500);
                }, 300);
            });
        }

        function initFireworks() {
            const canvas = document.getElementById("fireworks");
            const ctx = canvas.getContext("2d");
            canvas.width = window.innerWidth; canvas.height = window.innerHeight;
            let particles = [];
            function createPart(x,y){
                const color = `hsl(${Math.random()*360},100%,65%)`;
                for(let i=0;i<60;i++){
                    particles.push({x,y,dx:(Math.random()-0.5)*12, dy:(Math.random()-0.5)*12, life:90, color});
                }
            }
            function draw(){
                ctx.clearRect(0,0,canvas.width,canvas.height);
                particles.forEach((p,i)=>{
                    p.x+=p.dx; p.y+=p.dy; p.dy+=0.05; p.life--;
                    ctx.fillStyle=p.color;
                    ctx.beginPath(); ctx.arc(p.x,p.y,3,0,Math.PI*2); ctx.fill();
                    if(p.life<=0) particles.splice(i,1);
                });
                requestAnimationFrame(draw);
            }
            setInterval(()=>createPart(Math.random()*canvas.width, Math.random()*canvas.height*0.5), 400);
            draw();
        }
        /* YOUR EXISTING SCRIPT END */
    </script>
</body>
</html>