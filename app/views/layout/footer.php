</main>

<footer class="border-top bg-light">
  <div class="container py-4">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="fw-bold mb-2">Gọi mua hàng</div>
        <div class="text-muted">8:30 - 22:20 • Tất cả các ngày</div>
        <div class="fs-5 fw-semibold">083 267 2005</div>
      </div>
      <div class="col-md-4">
        <div class="fw-bold mb-2">Hỗ trợ khách hàng</div>
        <ul class="list-unstyled mb-0">
          <li><a class="text-decoration-none" href="#">Hướng dẫn mua hàng</a></li>
          <li><a class="text-decoration-none" href="#">Hướng dẫn chọn size</a></li>
          <li><a class="text-decoration-none" href="#">Chính sách đổi trả</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <div class="fw-bold mb-2">Đăng ký nhận tin</div>
        <form class="d-flex gap-2" onsubmit="return false;">
          <input class="form-control" placeholder="Email của bạn">
          <button class="btn btn-dark" type="button">Đăng ký</button>
        </form>
      </div>
    </div>

    <div class="small text-muted mt-4">© <?= date('Y') ?> ATINO-STYLE PHP SHOP (REAL DEMO)</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
  .chat-chip{border:1px solid #ddd;background:#fff;border-radius:999px;padding:6px 10px;font-size:12px;cursor:pointer}
  .chat-chip:hover{background:#111;color:#fff;border-color:#111}
</style>
<!-- CHATBOT WIDGET -->
<div id="chatFab" style="position:fixed;right:18px;bottom:18px;z-index:999999;">
  <button id="chatOpen"
    style="border:0;border-radius:999px;padding:14px 16px;
    cursor:pointer;background:#111;color:#fff;
    box-shadow:0 10px 24px rgba(0,0,0,.2);">
    💬 Chat
  </button>
</div>

<div id="chatBox"
style="display:none;position:fixed;right:18px;bottom:18px;width:360px;height:520px;
background:#fff;border-radius:14px;overflow:hidden;
box-shadow:0 10px 30px rgba(0,0,0,.25);z-index:999999;">

  <div style="background:#111;color:#fff;padding:10px">
    Chat hỗ trợ
    <button id="chatClose"
      style="float:right;background:none;border:0;color:white;font-size:18px;">
      ✕
    </button>
  </div>

  <div id="chatMsgs"
    style="height:360px;overflow:auto;padding:10px;background:#fafafa"></div>

  <div id="chatQuick" style="padding:8px 10px;border-top:1px solid #f0f0f0;background:#fff;display:flex;gap:6px;flex-wrap:wrap">
    <button type="button" class="chat-chip" data-msg="áo sơ mi trắng">Áo sơ mi trắng</button>
    <button type="button" class="chat-chip" data-msg="áo dưới 400k">Áo dưới 400k</button>
    <button type="button" class="chat-chip" data-msg="cao 170 nặng 65">Tư vấn size</button>
    <button type="button" class="chat-chip" data-msg="phí ship bao nhiêu">Phí ship</button>
    <button type="button" class="chat-chip" data-msg="thông tin sản phẩm 1">Thông tin SP 1</button>
    <button type="button" class="chat-chip" data-msg="so sánh 1 và 2">So sánh 1 và 2</button>
  </div>

  <form id="chatForm" style="display:flex;padding:10px">
    <input id="chatInput"
      style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px"
      placeholder="Nhập tin nhắn...">
    <button style="margin-left:6px;background:#111;color:white;border:0;padding:8px 12px;border-radius:6px">
      Gửi
    </button>
  </form>

</div>

<script>
const openBtn = document.getElementById("chatOpen");
const closeBtn = document.getElementById("chatClose");
const chatBox = document.getElementById("chatBox");
const chatFab = document.getElementById("chatFab");
const chatForm = document.getElementById("chatForm");
const chatInput = document.getElementById("chatInput");
const chatMsgs = document.getElementById("chatMsgs");
const chatQuick = document.getElementById("chatQuick");

openBtn.onclick = () => { chatBox.style.display = "block"; chatFab.style.display = "none"; chatInput.focus(); };
closeBtn.onclick = () => { chatBox.style.display = "none"; chatFab.style.display = "block"; };

function addMsg(text, side = "bot", allowHtml = false) {
  const isUser = side === "user";
  const wrap = document.createElement("div");
  wrap.style.margin = "6px 0";
  wrap.style.textAlign = isUser ? "right" : "left";

  const bubble = document.createElement("span");
  bubble.style.display = "inline-block";
  bubble.style.maxWidth = "85%";
  bubble.style.padding = "8px 10px";
  bubble.style.borderRadius = "10px";
  bubble.style.whiteSpace = "pre-line";
  bubble.style.wordBreak = "break-word";
  bubble.style.background = isUser ? "#111" : "#eee";
  bubble.style.color = isUser ? "#fff" : "#111";
  if (allowHtml) bubble.innerHTML = text;
  else bubble.textContent = text;

  wrap.appendChild(bubble);
  chatMsgs.appendChild(wrap);
  chatMsgs.scrollTop = chatMsgs.scrollHeight;
  return bubble;
}

async function requestBotReply(message) {
  const response = await fetch("<?= e(url('/api/chat')) ?>", {
    method: "POST",
    headers: { "Content-Type": "application/json", "Accept": "application/json" },
    body: JSON.stringify({ message })
  });
  if (!response.ok) throw new Error("API_ERROR");
  return response.json();
}

async function sendChatMessage(text) {
  if (!text) return;
  addMsg(text, "user");
  chatInput.value = "";
  const pending = addMsg("Đang trả lời...", "bot");

  try {
    const data = await requestBotReply(text);
    pending.textContent = data.reply || "Bot chưa có phản hồi.";
  } catch (error) {
    pending.textContent = "Xin lỗi, chatbot backend đang bận. Bạn thử lại giúp mình nhé.";
  }
}

chatForm.addEventListener("submit", async function (e) {
  e.preventDefault();
  const text = chatInput.value.trim();
  if (!text) return;
  await sendChatMessage(text);
});

chatQuick.addEventListener("click", async function (e) {
  const btn = e.target.closest(".chat-chip");
  if (!btn) return;
  const text = btn.getAttribute("data-msg") || "";
  await sendChatMessage(text);
});

addMsg("Xin chào 👋 Mình hỗ trợ tìm sản phẩm, giải thích thông tin sản phẩm, kiểm tra size/màu/tồn kho, so sánh mẫu, tư vấn size, tra cứu đơn hàng và vận đơn.", "bot");
</script>
</body>
</html>
