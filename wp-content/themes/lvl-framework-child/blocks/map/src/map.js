document.addEventListener("DOMContentLoaded", () => {
  const blocks = document.querySelectorAll(".block--map");

  function create_block_map() {
    return {
      block: null,
      init: function (block) {
        this.block = block;
        this.map();
      },
      map: function () {
        const mapWrapper = this.block.querySelector(".block--map-wrapper");
        let presetLocations = this.block?.dataset?.locations;
        if (presetLocations) presetLocations = JSON.parse(presetLocations);
        else presetLocations = [];

        let locations = [];

        const data = new FormData();
        data.append("action", "block_map_locations_get");
        data.append("nonce", lvl_block_map_ajax.nonce);
        data.append("preset_locations", presetLocations);
        fetch(lvl_block_map_ajax.ajax_url, {
          method: "POST",
          credentials: "same-origin",
          body: data,
        })
          .then((response) => {
            if (!response.ok) {
              console.log("Error getting locations");
              return;
            }

            return response.json();
          })
          .then((response) => {
            locations = response;

            if (!locations) return;

            const map = L.map(mapWrapper).setView([0, 0], 2);

            // Add a tile layer to the map
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
              // License attribution: https://operations.osmfoundation.org/policies/tiles/
              attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);

            let count = 0;
            locations.forEach((location) => {
              count++;
              var title = location.location.title;
              var linkedin = location.location?.linkedin;
              if (linkedin) {
                linkedin =
                  '<a class="map-linkedin" href="' +
                  linkedin +
                  '" target="_blank" rel="noopener noreferrer"><svg preserveAspectRatio = "xMidYMid slice" className = "icon icon-linkedin-in icon-sm" aria-hidden="true"><use xlink:href="#linkedin-in"></use></svg></a>';
              }

              var latitude = parseFloat(location.location?.lat);
              var longitude = parseFloat(location.location?.lng);
              if (isNaN(latitude) || isNaN(longitude)) {
                return;
              }

              var content = `<address>${location.location.address}</address>`;
              var phoneLink = location.location.phone;
              if (phoneLink) {
                content += `<p>Phone: <a href="tel:${phoneLink.url}">${phoneLink.title}</a></p>`;
              }
              // var getDirections = location.location.get_directions;
              // var titleUrlEncoded = title.replace(/ /g, '+');
              // titleUrlEncoded = encodeURI(titleUrlEncoded);

              // var addressUrlEncoded = location.location.mapAddress.replace(/ /g, '+');
              // addressUrlEncoded = encodeURI(addressUrlEncoded);

              // content += `<p><a href="https://www.google.com/maps/dir/?api=1&destination=${addressUrlEncoded}" target="_blank">Get Directions</a></p>`;

              if (location.location?.getDirections)
                content += `<p><a href="${location.location.getDirections}" target="_blank">Get Directions</a></p>`;

              var marker = L.marker([latitude, longitude], {
                title: title,
                alt: title,
                riseOnHover: true,
                draggable: false,
              }).addTo(map);

              let details = `
                                <h3 class="mb-2">${title}</h3>
                                <div>${content}</div>
                                ${linkedin}
                            `;

              marker.bindPopup(details, {});

              if (count === 1) {
                marker.bindPopup(details, { autoClose: false }).openPopup();
              } else {
                marker.bindPopup(details, { autoClose: false });
              }

              // marker.bindTooltip(title, {
              //     permanent: false,
              //     direction: 'auto',
              //     className: 'border-0 rounded',
              //     opacity: 1,
              // });

              marker.on("mouseover", function () {
                marker.openPopup();
              });
            });
            var bounds = L.latLngBounds(
              locations.map((location) => [
                location.location.lat,
                location.location.lng,
              ])
            );
            map.fitBounds(bounds, { padding: [100, 100] });
            // map.setZoom(16);

            map.scrollWheelZoom.disable();
          })
          .catch((error) => {
            console.error("Error fetching location data:", error);
            locations = null;
          });
      },
    };
  }

  blocks.forEach((block) => {
    const block_map = create_block_map();
    block_map.init(block);
  });
});
