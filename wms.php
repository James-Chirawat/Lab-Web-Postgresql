<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Leaflet + WMS ตัวอย่างง่าย</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    #map { height: 100vh; margin: 0; padding: 0; }
  </style>
</head>
<body>
  <div id="map"></div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    // สร้างแผนที่
    const map = L.map('map').setView([16.82, 100.26], 9);

    // พื้นหลัง OSM
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // WMS Layer
    const wms = L.tileLayer.wms('https://ogc.mapedia.co.th/geoserver/wms', {
      layers: 'nsru:amphoe',        // เปลี่ยนเป็น <workspace>:<layername> ตามที่ดูใน Layer Preview
      format: 'image/png',
      transparent: true,
      attribution: 'GeoServer'
    });

    // เพิ่มลงบนแผนที่
    wms.addTo(map);
  </script>
</body>
</html>
