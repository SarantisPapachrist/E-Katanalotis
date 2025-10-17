const openProfileBtn = document.getElementById('open-profile');
const panels = [
    document.getElementById('panel1'),
    document.getElementById('panel2'),
    document.getElementById('panel3'),
    document.getElementById('panel4')
];

openProfileBtn.addEventListener('click', () => {
    panels.forEach(panel => {
        panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
    });
});

const usernameForm = document.getElementById('usernameForm');
const passwordForm = document.getElementById('passwordForm');

usernameForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(usernameForm);
    fetch('update_user.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
      .then(data => {
          document.getElementById('usernameMessage').innerText = data.username || '';
      });
});

passwordForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(passwordForm);
    fetch('update_user.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
      .then(data => {
          document.getElementById('passwordMessage').innerText = data.password || '';
      });
});