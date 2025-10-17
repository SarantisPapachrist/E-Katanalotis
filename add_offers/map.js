let mymap = L.map('map').setView([38.246242, 21.7350847], 12);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(mymap);

let selectedShop = { name: "", id: "" };

fetch('../jsons/POI.json')
  .then(res => res.json())
  .then(data => {
    data.elements.forEach(el => {
      const name = el.tags.name || 'No Name';
      const category = el.tags.shop || 'No Category';
      const shopId = el.id;

      const popupContent = `
        <b>${name}</b><br>
        Category: ${category}<br><br>
        <button class="add-offer-btn" data-store="${name}" data-id="${shopId}" data-lat="${el.lat}" data-lon="${el.lon}">Add Offer</button>
        <button class="rate-offer-btn" data-store="${name}" data-id="${shopId}" data-lat="${el.lat}" data-lon="${el.lon}">Rate Offer</button>
      `;

      const marker = L.marker([el.lat, el.lon]).addTo(mymap);
      marker.bindPopup(popupContent);

      marker.on('popupopen', () => {
        const addBtn = marker.getPopup().getElement().querySelector('.add-offer-btn');
        const rateBtn = marker.getPopup().getElement().querySelector('.rate-offer-btn');

        addBtn.addEventListener('click', () => {
          const shopLatLng = L.latLng(addBtn.dataset.lat, addBtn.dataset.lon);

          if (!window.userPosition) {
            showMessage("Please click on your location on the map first!");
            return;
          }

          const distance = mymap.distance(window.userPosition, shopLatLng);
          if (distance > 200) {
            showMessage("You are too far from this shop to add an offer (must be within 200m).");
            return;
          }

          selectedShop.name = addBtn.dataset.store;
          selectedShop.id = addBtn.dataset.id;
          window.currentShopId = addBtn.dataset.id;

          document.getElementById('offer-panel').style.display = 'block';
          document.getElementById('shop-name-display').textContent = selectedShop.name;
        });

        rateBtn.addEventListener('click', () => {
          const shopLatLng = L.latLng(rateBtn.dataset.lat, rateBtn.dataset.lon);

          if (!window.userPosition) {
            showMessage("Please click on your location on the map first!");
            return;
          }

          const distance = mymap.distance(window.userPosition, shopLatLng);
          if (distance > 200) {
            showMessage("You are too far from this shop to rate offers (must be within 200m).");
            return;
          }

          const shopId = rateBtn.dataset.id;
          const shopName = rateBtn.dataset.store;
          document.getElementById('rate-shop-name').textContent = shopName;
          document.getElementById('rate-panel').style.display = 'block';

          refreshOffers(shopId);
        });
      });
    });
  });


function refreshOffers(shopId) {
  const container = document.getElementById('offers-container');
  container.innerHTML = "Loading...";

  fetch("get_offers.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ shop_id: shopId })
  })
  .then(res => res.json())
  .then(offers => {
    container.innerHTML = "";

    if (!offers.length) {
      container.innerHTML = "<p>No offers for this shop yet.</p>";
      return;
    }

    offers.forEach(offer => {
      const div = document.createElement('div');
      div.classList.add('offer-item');
      div.innerHTML = `
        <p><b>${offer.Product}</b> - €${offer.Price}</p>
        <button class="like-btn" data-id="${offer.offer_id}">👍 ${offer.Likes}</button>
        <button class="dislike-btn" data-id="${offer.offer_id}">👎 ${offer.Dislikes}</button>
      `;
      container.appendChild(div);

      div.querySelector('.like-btn').addEventListener('click', () => rateOffer(offer.offer_id, 'like', shopId));
      div.querySelector('.dislike-btn').addEventListener('click', () => rateOffer(offer.offer_id, 'dislike', shopId));
    });
  })
  .catch(err => {
    console.error(err);
    container.innerHTML = "<p>Error loading offers.</p>";
  });
}


function rateOffer(offerId, type, shopId) {
  fetch('rate_offer.php', {
    method: 'POST',
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ offer_id: offerId, type })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success && data.message.includes("already rated")) {
      showMessage(data.message);
    }
    refreshOffers(shopId); 
  })
  .catch(err => console.error('Error rating offer:', err));
}

document.getElementById("close-offer").addEventListener("click", () => {
    document.getElementById("offer-panel").style.display = "none";
});

document.getElementById("close-rate").addEventListener("click", () => {
    document.getElementById("rate-panel").style.display = "none";
});


function showMessage(text) {
    const msg = document.getElementById('message-panel');
    msg.textContent = text;
    msg.style.display = 'block';

    setTimeout(() => {
        msg.style.display = 'none';
    }, 4000);
}


const redIcon = L.icon({
    iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
    iconSize: [35, 35],    
    iconAnchor: [16, 32],    
    popupAnchor: [0, -32]    
});


let userMarker = null;
mymap.on('click', function(e) {
    const { lat, lng } = e.latlng;

    if (userMarker) {
        userMarker.setLatLng([lat, lng]);
    } else {
        userMarker = L.marker([lat, lng], { icon: redIcon }).addTo(mymap);
        userMarker.bindPopup("Your position").openPopup();
    }

    window.userPosition = { lat, lng };
    console.log("User clicked position:", lat, lng);
});

let categoriesData;
fetch('../add_offers/get_products2.php')
  .then(res => res.json())
  .then(data => {
    categoriesData = data;
    let catSelect = document.getElementById('offer-category');
    catSelect.innerHTML = '<option value="">Select Category</option>';
    for (let cat in data) {
      let opt = document.createElement('option');
      opt.value = cat;
      opt.textContent = cat;
      catSelect.appendChild(opt);
    }
  });

document.getElementById('offer-category').addEventListener('change', function() {
  let cat = this.value;
  let subSelect = document.getElementById('offer-subcategory');
  subSelect.innerHTML = '<option value="">Select Subcategory</option>';
  let prodSelect = document.getElementById('offer-product');
  prodSelect.innerHTML = '<option value="">Select Product</option>';
  if (!cat) return;
  for (let sub in categoriesData[cat]) {
    let opt = document.createElement('option');
    opt.value = sub;
    opt.textContent = sub;
    subSelect.appendChild(opt);
  }
});

document.getElementById('offer-subcategory').addEventListener('change', function() {
  let cat = document.getElementById('offer-category').value;
  let sub = this.value;
  let prodSelect = document.getElementById('offer-product');
  prodSelect.innerHTML = '<option value="">Select Product</option>';
  if (!cat || !sub) return;
  for (let prod of categoriesData[cat][sub]) {
    let opt = document.createElement('option');
    opt.value = prod;
    opt.textContent = prod;
    prodSelect.appendChild(opt);
  }
});

function showOfferMessage(text, isError = false) {
  const msg = document.getElementById("offer-message");
  msg.textContent = text;
  msg.style.color = isError ? "red" : "green";
  msg.style.display = "block";

  setTimeout(() => {
    msg.style.display = "none";
  }, 4000);
}

document.getElementById("submit-offer").addEventListener("click", async () => {
  const offer = {
    category: document.getElementById("offer-category").value,
    subcategory: document.getElementById("offer-subcategory").value,
    product: document.getElementById("offer-product").value,
    price: document.getElementById("offer-price").value,
    shop_name: document.getElementById("shop-name-display").textContent,
    shop_id: window.currentShopId || ""
  };

  console.log("Submitting offer:", offer); 

  try {
    const response = await fetch("add_offer.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(offer)
    });

    const data = await response.json();
    console.log("Server response:", data); 

    if (data.success) {
      showOfferMessage(data.message || "Offer added successfully!");
      document.getElementById("offer-price").value = "";
    } else {
      showOfferMessage(data.message || "Failed to add offer", true);
    }
  } catch (error) {
    console.error("Error submitting offer:", error);
    showOfferMessage("Failed to send offer. Check console for details.", true);
  }
});

var timeoutDuration = 600000; //10 minutes
var timeout = setTimeout(function() {
    window.location.href = "../user/login.php";
}, timeoutDuration);

window.addEventListener('mousemove', resetTimer);
window.addEventListener('click', resetTimer);

function resetTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(function() {
        window.location.href = "../user/login.php";
    }, timeoutDuration);
}