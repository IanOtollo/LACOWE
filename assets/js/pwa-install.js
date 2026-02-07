/**
 * PWA Installation Prompt Handler
 */

let deferredPrompt;
let installButton;

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', () => {
  // Create install button
  createInstallButton();
  
  // Register service worker
  if ('serviceWorker' in navigator) {
    registerServiceWorker();
  }
  
  // Handle install prompt
  handleInstallPrompt();
  
  // Check if already installed
  checkIfInstalled();
});

function createInstallButton() {
  const installContainer = document.createElement('div');
  installContainer.id = 'pwa-install-container';
  installContainer.style.cssText = `
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    display: none;
  `;
  
  installButton = document.createElement('button');
  installButton.id = 'pwa-install-button';
  installButton.innerHTML = '📱 Install App';
  installButton.style.cssText = `
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
    transition: all 0.3s ease;
  `;
  
  installButton.addEventListener('mouseover', () => {
    installButton.style.transform = 'translateY(-2px)';
    installButton.style.boxShadow = '0 6px 16px rgba(30, 64, 175, 0.4)';
  });
  
  installButton.addEventListener('mouseout', () => {
    installButton.style.transform = 'translateY(0)';
    installButton.style.boxShadow = '0 4px 12px rgba(30, 64, 175, 0.3)';
  });
  
  installButton.addEventListener('click', installApp);
  
  installContainer.appendChild(installButton);
  document.body.appendChild(installContainer);
}

function registerServiceWorker() {
  navigator.serviceWorker.register('/lacowe-welfare-mis/service-worker.js')
    .then(registration => {
      console.log('Service Worker registered:', registration);
      
      // Check for updates
      registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing;
        newWorker.addEventListener('statechange', () => {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            // New version available
            showUpdateNotification();
          }
        });
      });
    })
    .catch(error => {
      console.error('Service Worker registration failed:', error);
    });
}

function handleInstallPrompt() {
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button
    document.getElementById('pwa-install-container').style.display = 'block';
  });
  
  window.addEventListener('appinstalled', () => {
    console.log('PWA installed successfully');
    document.getElementById('pwa-install-container').style.display = 'none';
    deferredPrompt = null;
    
    // Show success message
    showNotification('App installed successfully! You can now use LACOWE MIS offline.', 'success');
  });
}

async function installApp() {
  if (!deferredPrompt) {
    return;
  }
  
  // Show install prompt
  deferredPrompt.prompt();
  
  // Wait for user choice
  const { outcome } = await deferredPrompt.userChoice;
  console.log(`User ${outcome} the install prompt`);
  
  deferredPrompt = null;
  document.getElementById('pwa-install-container').style.display = 'none';
}

function checkIfInstalled() {
  // Check if running as installed app
  if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
    console.log('App is running in standalone mode');
    document.getElementById('pwa-install-container').style.display = 'none';
    
    // Add app-specific styles
    document.body.classList.add('pwa-installed');
  }
}

function showUpdateNotification() {
  const notification = document.createElement('div');
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    max-width: 300px;
  `;
  
  notification.innerHTML = `
    <div style="margin-bottom: 12px; font-weight: 600; color: #1e40af;">
      Update Available
    </div>
    <div style="margin-bottom: 12px; font-size: 14px; color: #4b5563;">
      A new version of LACOWE MIS is available.
    </div>
    <button onclick="location.reload()" style="
      background: #1e40af;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    ">
      Update Now
    </button>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 10000);
}

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === 'success' ? '#10b981' : '#3b82f6'};
    color: white;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    max-width: 300px;
    font-size: 14px;
  `;
  
  notification.textContent = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease';
    setTimeout(() => notification.remove(), 300);
  }, 5000);
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
  setTimeout(() => {
    Notification.requestPermission().then(permission => {
      console.log('Notification permission:', permission);
    });
  }, 5000);
}
