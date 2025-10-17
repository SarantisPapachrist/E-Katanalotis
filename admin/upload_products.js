let selectedFile = null;

document.getElementById("uploadProductsBtn").addEventListener("click", async () => {
  const fileInput = document.getElementById("productFileInput");
  const status = document.getElementById("status");

  if (!fileInput.files.length) {
    status.textContent = "⚠️ Please select a JSON file.";
    return;
  }

  const file = fileInput.files[0];
  const text = await file.text();

  try {
    const res = await fetch("upload_products_backend.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: text
    });

    const result = await res.text();
    status.textContent = result;
  } catch (err) {
    status.textContent = "❌ Upload failed: " + err.message;
  }
});

