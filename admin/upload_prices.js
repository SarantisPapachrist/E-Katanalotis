let selectedFile = null;

document.getElementById('priceFileInput').addEventListener('change', function(e) {
  selectedFile = e.target.files[0];
  document.getElementById('status').textContent = selectedFile ? "File selected: " + selectedFile.name : "No file chosen.";
});

document.getElementById('uploadPricesBtn').addEventListener('click', function() {
  if (!selectedFile) {
    document.getElementById('status').textContent = "⚠️ Please select a file first.";
    return;
  }

  const reader = new FileReader();
  reader.onload = function(evt) {
    try {
      const jsonData = JSON.parse(evt.target.result);

      fetch('save_prices.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(jsonData)
      })
      .then(res => res.text())
      .then(response => {
        document.getElementById('status').innerHTML = "✅ " + response;
      })
      .catch(err => {
        document.getElementById('status').textContent = "❌ Error uploading: " + err;
      });
    } catch (err) {
      document.getElementById('status').textContent = "❌ Invalid JSON file!";
    }
  };
  reader.readAsText(selectedFile);
});
