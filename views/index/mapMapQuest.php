<?php

/** @var \Ilch\View $this */

if ($this->get('memberlocations')) : ?>
<script src="https://api.mqcdn.com/sdk/mapquest-js/v1.3.2/mapquest.js"></script>
<link type="text/css" rel="stylesheet" href="https://api.mqcdn.com/sdk/mapquest-js/v1.3.2/mapquest.css"/>

<script src="https://unpkg.com/leaflet.markercluster@1.0.6/dist/leaflet.markercluster.js"></script>
<link type="text/css" rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.0.6/dist/MarkerCluster.css"/>
<link type="text/css" rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.0.6/dist/MarkerCluster.Default.css"/>
<div class="text-center">
    <?php if ($this->getUser()) { ?>
    <a class="btn btn-outline-secondary" href="<?= $this->getUrl(['action' => 'treat'])?>" title="<?= $this->getTrans('mapEntry')?>"><?= $this->getTrans('mapEntry')?></a> &nbsp;
    <?php } ?>
    <a class="btn btn-outline-secondary" href="<?= $this->getUrl(['action' => 'map'])?>" title="<?= $this->getTrans('mapView')?>"><?= $this->getTrans('mapView')?></a>
</div>
<br>
<?php 
$city_array = [];
$out_array = [];
/** @var Modules\Membermap\Models\MemberMap $location */
foreach ($this->get('memberlocations') as $location) {
    if ($location->getCity() != "") {
        $name = $location->getName();
        $zip_code = $location->getZipCode();
        $country_code = $location->getCountryCode();
        if ($location->getStreet() != "") {
            $address = $location->getStreet() . ', ' . $location->getCity();
        } else {
            $address = $location->getCity();
        }
        $address = strtolower($address);
        $address = str_replace(array('�','�','�','�'), array('ae', 'ue', 'oe', 'ss'), $address );
        $address = preg_replace("/[^a-z0-9\_\s]/", "", $address);
        $address = str_replace( array(' ', '--'), array('-', '-'), $address );

        $city_array = [
            "names" => $name,
            "zip_code" => $zip_code,
            "address" => $address,
            "country_code" => $country_code,
        ];

        $out_array[] = $city_array;
    }
}

?>
<script type="text/javascript">
    window.onload = function() {

        L.mapquest.key = '<?php echo $this->get('apiKey');?>';

        var geocoder = L.mapquest.geocoding();
        geocoder.geocode([<?php foreach ($out_array as $city) {
                              echo "'$city[address], $city[zip_code], $city[country_code]'"; echo ", ";
                          } ?>], createMap);

        function createMap(error, response) {
            // Initialize the Map
            var map = L.mapquest.map('map', {
                layers: L.mapquest.tileLayer('map'),
                center: [0, 0],
                zoom: 6
            });

            // Generate the feature group containing markers from the geocoded locations
            var featureGroup = generateMarkersFeatureGroup(response);

            // Add markers to the map and zoom to the features
            featureGroup.addTo(map);
            map.fitBounds(featureGroup.getBounds());
        }
        var js_array =<?=json_encode($out_array); ?>;
        var markers = L.markerClusterGroup();

        function generateMarkersFeatureGroup(response) {
            var group = [];
            for (var i = 0; i < response.results.length; i++) {
                var location = response.results[i].locations[0];
                var locationLatLng = location.latLng;

                // Create a marker for each location
                var title = js_array[i].names;
                var marker = L.marker(locationLatLng, {title: title, icon: L.mapquest.icons.marker()})

                marker.bindPopup(title);
                markers.addLayer(marker);

                group.push(markers);
            }
            return L.featureGroup(group);
        }
    }
</script>
<div id='map' style='width: 100%; height:530px;'></div>
<?php else: ?>
<div class="alert alert-danger">
    <?=$this->getTrans('noEntries') ?>
</div>
<?php endif; ?>
