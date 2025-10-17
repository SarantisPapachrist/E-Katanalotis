let mymap = L.map('admin-map');
let osmUrl = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
let osmAttrib = 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
L.tileLayer(osmUrl, { attribution: osmAttrib }).addTo(mymap);
mymap.setView([38.246242, 21.7350847], 11);

function showMessage(text, type = 'success') {
    let msg = document.getElementById('admin-message');
    if (!msg) {
        msg = document.createElement('div');
        msg.id = 'admin-message';
        msg.style.position = 'fixed';
        msg.style.top = '15px';
        msg.style.left = '250px';
        msg.style.padding = '10px 20px';
        msg.style.borderRadius = '5px';
        msg.style.zIndex = '9999';
        msg.style.fontWeight = 'bold';
        document.body.appendChild(msg);
    }
    msg.textContent = text;
    msg.style.backgroundColor = type === 'success' ? 'green' : 'red';
    msg.style.color = 'white';
    msg.style.display = 'block';
    setTimeout(() => { msg.style.display = 'none'; }, 4000);
}

let poiRequest = new XMLHttpRequest();
poiRequest.open('GET', '../jsons/POI.json', true);
poiRequest.send();

poiRequest.onload = function() {
    if (this.readyState === 4 && this.status === 200) {
        let data = JSON.parse(this.responseText);

        data.elements.forEach(element => {
            if (element.type === "node" && element.tags && element.tags.shop === "supermarket") {
                let name = element.tags.name || 'No Name';
                let category = element.tags.shop || 'No Category';

                let popupContent = `
                    <b>${name}</b><br>
                    Category: ${category}<br><br>
                    <button class="delete-offer-btn" data-store="${name}">Delete Offers</button>
                `;

                let marker = L.marker([element.lat, element.lon]).addTo(mymap);
                marker.bindPopup(popupContent);

                marker.on('popupopen', () => {
                    const popupEl = marker.getPopup().getElement();
                    const deleteBtn = popupEl.querySelector('.delete-offer-btn');
                    if (!deleteBtn) return;

                    deleteBtn.addEventListener('click', () => {
                        const storeName = deleteBtn.dataset.store;

                        fetch(`get_shop_offers.php?shop_name=${encodeURIComponent(storeName)}`)
                        .then(res => res.json())
                        .then(offers => {
                            if (!offers || offers.length === 0) {
                                showMessage(`No offers found for ${storeName}`, 'error');
                                return;
                            }

                            let panel = document.getElementById('admin-offers-panel');
                            if (!panel) {
                                panel = document.createElement('div');
                                panel.id = 'admin-offers-panel';
                                panel.style.position = 'fixed';
                                panel.style.top = '50px';
                                panel.style.right = '20px';
                                panel.style.width = '350px';
                                panel.style.maxHeight = '400px';
                                panel.style.overflowY = 'auto';
                                panel.style.background = '#f5f5f5';
                                panel.style.border = '1px solid #ccc';
                                panel.style.padding = '15px';
                                panel.style.zIndex = '9999';
                                panel.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
                                document.body.appendChild(panel);

                                let closeBtn = document.createElement('button');
                                closeBtn.textContent = 'Close';
                                closeBtn.style.marginBottom = '10px';
                                closeBtn.addEventListener('click', () => { panel.style.display = 'none'; });
                                panel.appendChild(closeBtn);

                                let title = document.createElement('h3');
                                title.id = 'admin-offers-title';
                                panel.appendChild(title);

                                let container = document.createElement('div');
                                container.id = 'admin-offers-container';
                                panel.appendChild(container);
                            }

                            const container = document.getElementById('admin-offers-container');
                            const title = document.getElementById('admin-offers-title');

                            container.innerHTML = '';
                            title.innerText = `Offers for ${storeName}`;

                            offers.forEach(offer => {
                                const div = document.createElement('div');
                                div.classList.add('offer-item');
                                div.style.borderBottom = '1px solid #ccc';
                                div.style.marginBottom = '10px';
                                div.style.paddingBottom = '5px';
                                div.innerHTML = `
                                    <p><strong>Product:</strong> ${offer.Product}</p>
                                    <p><strong>Category:</strong> ${offer.Category} / ${offer.SubCategory}</p>
                                    <p><strong>Price:</strong> €${offer.Price}</p>
                                    <button class="delete-offer-item" data-offerid="${offer.offer_id}">Delete</button>
                                `;
                                container.appendChild(div);

                                const btn = div.querySelector('.delete-offer-item');
                                btn.addEventListener('click', () => {
                                    const offerId = btn.dataset.offerid;
                                    fetch('delete_offer.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ offer_id: offerId })
                                    })
                                    .then(res => res.json())
                                    .then(resp => {
                                        showMessage(resp.message, resp.success ? 'success' : 'error');
                                        if (resp.success) div.remove();
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        showMessage('Error deleting offer', 'error');
                                    });
                                });
                            });

                            panel.style.display = 'block';
                        })
                        .catch(err => {
                            console.error(err);
                            showMessage('Error loading offers', 'error');
                        });
                    });
                });
            }
        });
    }
};

document.getElementById('close-admin-offers').addEventListener('click', function () {
  document.getElementById('admin-offers-panel').style.display = 'none';
});
