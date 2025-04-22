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
    $schools = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>GIS Map – โรงเรียนในจังหวัดพิษณุโลก</title>

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <!-- MarkerCluster CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
  <!-- Fullscreen Control CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen/Control.FullScreen.css" />
  <!-- Leaflet.draw CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>

  <style>
    html, body { height: 100%; margin: 0; padding: 0; }
    #sidebar {
      position: absolute; top: 0; left: 0; width: 300px; height: 100%;
      background: #f8f9fa; padding: 10px; box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      overflow-y: auto; font-family: sans-serif;
    }
    #sidebar h2 { margin: 0 0 10px; font-size: 18px; }
    #sidebar input {
      width: 100%; padding: 6px 8px; margin-bottom: 10px;
      border: 1px solid #ccc; border-radius: 4px;
    }
    #sidebar ul { list-style: none; padding: 0; margin: 0; }
    #sidebar li {
      padding: 6px 8px; border-bottom: 1px solid #e0e0e0; cursor: pointer;
    }
    #sidebar li:hover { background: #e2e6ea; }

    /* พื้นที่แสดงพิกัดเส้นที่วาด */
    #coordsOutput {
      white-space: pre-wrap; margin-top: 15px;
      padding: 6px; background: #fff; border: 1px solid #ccc;
      border-radius: 4px; font-size: 13px; max-height: 150px; overflow-y: auto;
    }

    #map {
      position: absolute; top: 0; bottom: 0; left: 300px; right: 0;
    }
  </style>
</head>
<body>
  <div id="sidebar">
    <h2>รายชื่อโรงเรียน</h2>
    <input type="text" id="searchInput" placeholder="🔍 ค้นหาโรงเรียน...">
    <ul id="schoolList"></ul>

    <h2>พิกัดเส้นที่วาด</h2>
    <div id="coordsOutput">(รอวาดเส้นเพื่อแสดงพิกัด)</div>
  </div>

  <div id="map"></div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- MarkerCluster JS -->
  <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
  <!-- Fullscreen Control JS -->
  <script src="https://unpkg.com/leaflet.fullscreen/Control.FullScreen.min.js"></script>
  <!-- Leaflet.draw JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

  <script>
    const schools = <?php echo json_encode($schools, JSON_UNESCAPED_UNICODE); ?>;

    // สร้าง map พร้อม fullscreen
    const map = L.map('map', { fullscreenControl: true })
      .setView([16.82, 100.26], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.control.scale({ imperial: false }).addTo(map);

    // Marker cluster
    const markers = L.markerClusterGroup();
    const listEl = document.getElementById('schoolList');

    schools.forEach(s => {
      const lat = parseFloat(s.lat), lon = parseFloat(s.lon);
      if (isNaN(lat)||isNaN(lon)) return;

      const marker = L.marker([lat, lon], { title: s.name });
      marker.bindPopup(`<strong>${s.name}</strong><br>${s.address}<br>${s.province} ${s.postcode}`);
      markers.addLayer(marker);

      const li = document.createElement('li');
      li.textContent = s.name;
      li.onclick = () => { map.setView([lat, lon], 17); marker.openPopup(); };
      listEl.appendChild(li);
    });

    map.addLayer(markers);

    // Search filter
    document.getElementById('searchInput').addEventListener('input', e => {
      const kw = e.target.value.toLowerCase();
      Array.from(listEl.children).forEach(li => {
        li.style.display = li.textContent.toLowerCase().includes(kw) ? '' : 'none';
      });
    });

    // Setup Leaflet.draw
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
      edit: { featureGroup: drawnItems, remove: true },
      draw: {
        polyline: true,
        polygon: false,
        rectangle: false,
        circle: false,
        marker: false,
        circlemarker: false
      }
    });
    map.addControl(drawControl);

    // Event เมื่อวาดเสร็จ
    map.on(L.Draw.Event.CREATED, function (e) {
      const layer = e.layer;
      drawnItems.addLayer(layer);

      if (e.layerType === 'polyline') {
        const latlngs = layer.getLatLngs();
        // แสดงพิกัดแต่ละจุดใน sidebar
        const out = latlngs.map(ll => `[${ll.lat.toFixed(6)}, ${ll.lng.toFixed(6)}]`).join('\n');
        document.getElementById('coordsOutput').textContent = out;
      }
    });
  </script>
</body>
</html>
