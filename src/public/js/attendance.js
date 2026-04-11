const timeElement = document.getElementById('current-time');
let serverTime = parseInt(timeElement.dataset.timestamp, 10);

function updateTime() {
  const now = new Date(serverTime);

  const year = now.getFullYear();
  const month = now.getMonth() + 1;
  const date = now.getDate();
  const days = ['日', '月', '火', '水', '木', '金', '土'];
  const dayOfWeek = days[now.getDay()];
  const dateString = `${year}年${month}月${date}日(${dayOfWeek})`;

  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const timeString = `${hours}:${minutes}`;

  document.getElementById('current-date').innerText = dateString;
  document.getElementById('current-time').innerText = timeString;

  serverTime += 1000;
}

setInterval(updateTime, 1000);
