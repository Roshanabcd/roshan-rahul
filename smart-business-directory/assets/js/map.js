// Google Maps Integration
let map;
let markers = [];
let currentInfoWindow = null;

function initMap() {
    const defaultLocation = { lat: 27.7172, lng: 85.3240 }; // Center of Kathmandu, Nepal
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 7,
        center: defaultLocation,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        zoomControl: true
    });
    
    // Load businesses on map
    loadBusinessesOnMap();
}

function loadBusinessesOnMap() {
    const bounds = new google.maps.LatLngBounds();
    
    fetch('ajax/nearby-businesses.php')
        .then(response => response.json())
        .then(businesses => {
            businesses.forEach(business => {
                if (business.latitude && business.longitude) {
                    const position = { lat: parseFloat(business.latitude), lng: parseFloat(business.longitude) };
                    
                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: business.biz_name,
                        animation: google.maps.Animation.DROP,
                        icon: {
                            url: business.logo_url || '/assets/images/marker-icon.png',
                            scaledSize: new google.maps.Size(40, 40)
                        }
                    });
                    
                    const infoContent = `
                        <div style="min-width: 200px;">
                            <h6>${escapeHtml(business.biz_name)}</h6>
                            <p class="mb-1 small">${escapeHtml(business.cat_name)}</p>
                            <p class="mb-1 small"><i class="fas fa-star text-warning"></i> ${business.avg_rating || 0} (${business.review_count || 0} reviews)</p>
                            <a href="business-detail.php?id=${business.biz_id}" class="btn btn-sm btn-primary mt-2">View Details</a>
                        </div>
                    `;
                    
                    const infoWindow = new google.maps.InfoWindow({
                        content: infoContent
                    });
                    
                    marker.addListener('click', () => {
                        if (currentInfoWindow) currentInfoWindow.close();
                        currentInfoWindow = infoWindow;
                        infoWindow.open(map, marker);
                    });
                    
                    markers.push(marker);
                    bounds.extend(position);
                }
            });
            
            if (markers.length > 0) {
                map.fitBounds(bounds);
            }
        })
        .catch(error => console.error('Error loading businesses:', error));
}

// Location picker for business registration
function initLocationPicker() {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('address');
    
    if (!latInput || !lngInput) return;
    
    const defaultLocation = { lat: 27.7172, lng: 85.3240 };
    const currentLat = parseFloat(latInput.value);
    const currentLng = parseFloat(lngInput.value);
    const center = (currentLat && currentLng) ? { lat: currentLat, lng: currentLng } : defaultLocation;
    
    const pickerMap = new google.maps.Map(document.getElementById('locationPicker'), {
        zoom: 13,
        center: center,
        mapTypeId: 'roadmap'
    });
    
    let marker = null;
    
    if (currentLat && currentLng) {
        marker = new google.maps.Marker({
            position: center,
            map: pickerMap,
            draggable: true,
            animation: google.maps.Animation.DROP
        });
        
        marker.addListener('dragend', function() {
            updateLocation(marker.getPosition().lat(), marker.getPosition().lng());
        });
    }
    
    pickerMap.addListener('click', function(e) {
        if (marker) marker.setMap(null);
        
        marker = new google.maps.Marker({
            position: e.latLng,
            map: pickerMap,
            draggable: true,
            animation: google.maps.Animation.DROP
        });
        
        marker.addListener('dragend', function() {
            updateLocation(marker.getPosition().lat(), marker.getPosition().lng());
        });
        
        updateLocation(e.latLng.lat(), e.latLng.lng());
        
        // Reverse geocoding to get address
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: e.latLng }, function(results, status) {
            if (status === 'OK' && results[0] && addressInput) {
                addressInput.value = results[0].formatted_address;
            }
        });
    });
    
    function updateLocation(lat, lng) {
        latInput.value = lat;
        lngInput.value = lng;
    }
}

// Get user's current location
function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setCenter({ lat: lat, lng: lng });
            map.setZoom(14);
            
            new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                title: 'Your Location',
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                }
            });
        }, function() {
            alert('Unable to get your location');
        });
    } else {
        alert('Geolocation is not supported');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}