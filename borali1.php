<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locais em São Caetano do Sul</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <style>
        body { font-family: Arial, sans-serif; background-color: #0a043c; color: white; margin: 0; padding: 0; }
        header { display: flex; align-items: center; padding: 15px; background-color: #10102f; }
        header img { border-radius: 50%; width: 40px; height: 40px; margin-right: 15px; }
        header input { width: 80%; padding: 10px; border: none; border-radius: 5px; }
        .container { padding: 15px; }
        .location { margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .location img { width: 100%; border-radius: 10px; }
        .location h2 { margin: 10px 0 5px 0; font-size: 18px; }
        .location p { margin: 0; font-size: 14px; color: #bbb; }
        .location .distance { float: right; color: #bbb; }
        .location button { margin-top: 10px; background-color: #ff5733; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        .location button:hover { background-color: #ff2e00; }
        .navbar { position: fixed; bottom: 0; width: 100%; background-color: #10102f; display: flex; justify-content: space-around; padding: 10px 0; }
        .navbar img { width: 25px; }
        #map { height: 300px; width: 100%; margin-top: 15px; }
    </style>
</head>
<body>

<header>
    <img src="profile.png" alt="Profile">
    <input type="text" placeholder="Pesquisar">
</header>

<div class="container">
    <h3>Lugares próximos de você</h3>
    <div id="locations-list"></div>
</div>

<div id="map"></div>

<div class="navbar">
    <img src="search_icon.png" alt="Search">
    <img src="favorites_icon.png" alt="Favorites">
    <img src="filter_icon.png" alt="Filter">
</div>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
let map, routingControl;

// Verificação da geolocalização do navegador
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition, showError);
} else {
    alert("Geolocalização não é suportada por este navegador.");
}

// Localização obtida com sucesso
function showPosition(position) {
    var userLat = position.coords.latitude;
    var userLon = position.coords.longitude;

    // Inicializa o mapa na localização do usuário
    map = L.map('map').setView([userLat, userLon], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Marca posição
    L.marker([userLat, userLon]).addTo(map)
        .bindPopup('<b>Você está aqui</b>').openPopup();

    // Locais de interesse
    var places = [
        { name: 'Vivano', lat: -23.616141, lon: -46.565172, img: 'imgborali/vivano.jpg', description: 'Experiência gastronômica' },
        { name: 'Senhor Boteco', lat: -23.616051, lon: -46.570273, img: 'imgborali/senhor_boteco.jpg', description: 'Opção de bar pela cidade' },
        { name: 'USCS Conceição', lat: -23.617801043838988, lon: -46.57895393138371, img: 'imgborali/uscs.jpg', description: 'Universidade' },
        { name: 'Ironberg', lat: -23.623600206635096, lon: -46.57709933812019, img: 'imgborali/ironberg.jpg', description: 'Academia Ironberg' },
        { name: 'Park Shopping São Caetano', lat: -23.62577071710919, lon: -46.58031329507067, img: 'shopping_scs.jpg', description: 'Shopping Center' },
        { name: 'Escola Mario Leandro', lat: -23.691804897254574, lon: -46.43650704828672, img: 'imgborali/escola.jpg', description: 'Escola pública' },
        { name: 'Praça Hiroshiro', lat: -23.692248077859876, lon: -46.43890818397629, img: 'imgborali/praça.jpg', description: 'Praça Pública' },
        { name: 'Parque da Juventude', lat: -23.506975294310987, lon: -46.62042587515163, img: 'imgborali/parque_juventude.jpg', description: 'Parque da Juventude' },
    ];

    var locationsList = document.getElementById('locations-list');

    // Função para calcular a distância entre duas coordenadas
    function calculateDistance(lat1, lon1, lat2, lon2) {
        var R = 6371; // Raio da Terra em km
        var dLat = (lat2 - lat1) * (Math.PI / 180);
        var dLon = (lon2 - lon1) * (Math.PI / 180);
        var a = 
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) * 
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var distance = R * c; // Distância em km
        return distance;
    }

    // Calcula a distância e exibe locais
    places.forEach(function(place) {
        var distance = calculateDistance(userLat, userLon, place.lat, place.lon);

        if (distance <= 2) {
            L.marker([place.lat, place.lon]).addTo(map)
                .bindPopup('<b>' + place.name + '</b><br>Distância: ' + distance.toFixed(2) + ' km');

            // Cria a lista de locais com botão "Definir rota"
            locationsList.innerHTML += `
                <div class="location">
                    <img src="${place.img}" alt="${place.name}">
                    <h2>${place.name} <span class="distance">${distance.toFixed(2)} km</span></h2>
                    <p>${place.description}</p>
                    <button onclick="defineRoute(${userLat}, ${userLon}, ${place.lat}, ${place.lon})">Definir Rota</button>
                </div>
            `;
        }
    });
}

// Função para definir a rota
function defineRoute(userLat, userLon, destLat, destLon) {
    if (routingControl) {
        map.removeControl(routingControl); // Remove rota anterior, se existir
    }

    // Configura rota com Leaflet Routing Machine
    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(userLat, userLon),
            L.latLng(destLat, destLon)
        ],
        routeWhileDragging: true,
        lineOptions: {
            styles: [{ color: '#ff5733', opacity: 0.8, weight: 5 }]
        }
    }).addTo(map);
}

// Erro de localização
function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert("Usuário negou a solicitação de geolocalização.");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("As informações de localização não estão disponíveis.");
            break;
        case error.TIMEOUT:
            alert("A solicitação de geolocalização expirou.");
            break;
        case error.UNKNOWN_ERROR:
            alert("Ocorreu um erro desconhecido.");
            break;
    }
}
</script>

</body>
</html>
