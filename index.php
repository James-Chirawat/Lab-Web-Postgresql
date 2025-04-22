<?php
// ดึงข้อมูลโรงเรียนจาก PostgreSQL
$host     = 'localhost';
$port     = '5432';
$dbname   = 'geodb';
$user     = 'postgres';
$password = '1234';
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $pdo->query('SELECT id, name, address, province, postcode, lat, lon FROM school');
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // ถ้าเชื่อมไม่สำเร็จ ให้ schools เป็น array ว่าง
    $schools = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>GIS Map – โรงเรียนในจังหวัดพิษณุโลก</title>
  <style>
    /* ให้ map เต็มหน้าจอ */
    html, body, #map { height: 100%; margin: 0; padding: 0; }
  </style>

  <!-- Leaflet CSS (ไม่ต้องใช้ SRI) -->
  <link 
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
</head>
<body>

  <div id="map"></div>

  <!-- Leaflet JS (ไม่ต้องใช้ SRI) -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
    // สร้างแผนที่และตั้งตำแหน่งเริ่มต้น
    const map = L.map('map').setView([16.82, 100.26], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // ดึงข้อมูลจาก PHP ที่ฝังเป็น JS variable
    const schools = <?php echo json_encode($schools, JSON_UNESCAPED_UNICODE); ?>;

    schools.forEach(s => {
      const lat = parseFloat(s.lat),
            lon = parseFloat(s.lon);
      if (!isNaN(lat) && !isNaN(lon)) {
        L.marker([lat, lon])
         .addTo(map)
         .bindPopup(`
           <strong>${s.name}</strong><br>
           ${s.address}<br>
           ${s.province} ${s.postcode}
         `);
      }
    });
  </script>
</body>
</html>
