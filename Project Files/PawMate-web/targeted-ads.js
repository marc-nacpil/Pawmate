// Store popup and overlay elements globally
let adPopup = null;
let adOverlay = null;

async function getUserPetPreference() {
    try {
        console.log('Fetching user preference...');
        const response = await fetch('user-preference.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Received preference data:', data);
        
        if (!data.preferred_pet) {
            throw new Error('No preferred_pet in response');
        }
        
        const preference = data.preferred_pet.toLowerCase();
        
        if (!['dog', 'cat'].includes(preference)) {
            throw new Error(`Invalid pet preference: ${preference}`);
        }
        
        return preference;
    } catch (error) {
        console.error('Error fetching user preference:', error);
        return null;
    }
}

const advertisements = {
    'dog': [
        {
            type: 'video',
            title: "TropiClean® Natural Flea and Tick Dog Shampoo",
            mediaUrl: "./assets/video-ads/Dog_Ads1.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "Dog Tested, Dog Approved!",
            mediaUrl: "./assets/video-ads/Dog_Ads2.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "Yum Yum Dog Food: Ang choice ng Bark-Ada ko!",
            mediaUrl: "./assets/video-ads/Dog_Ads3.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "Doggy Dentures, Pedigree Dentastix!",
            mediaUrl: "./assets/video-ads/Dog_Ads4.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "How To Clicker Train Your Dog",
            mediaUrl: "./assets/video-ads/Dog_Ads5.mp4",
            link: "#"
        }
        
    ],
    'cat': [
        {
            type: 'video',
            title: "How to Train Your Cat to Use a Litter Box?",
            mediaUrl: "./assets/video-ads/Cat_Ads1.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "How to Care for Your New Cat?",
            mediaUrl: "./assets/video-ads/Cat_Ads2.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "DIY Tips For Cat Paradise!",
            mediaUrl: "./assets/video-ads/Cat_Ads3.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "How To Make DIY Cat Toys!",
            mediaUrl: "./assets/video-ads/Cat_Ads4.mp4",
            link: "#"
        },
        {
            type: 'video',
            title: "New Whiskas® Improved Recipe!",
            mediaUrl: "./assets/video-ads/Cat_Ads5.mp4",
            link: "#"
        }
    ]
};

function createPopupElements() {
    // Create overlay
    adOverlay = document.createElement('div');
    adOverlay.className = 'ad-overlay';
    document.body.appendChild(adOverlay);
    
    // Create popup with larger default width
    adPopup = document.createElement('div');
    adPopup.className = 'ad-popup';
    adPopup.style.width = '800px'; // Increased width for better video display
    adPopup.style.maxWidth = '90vw'; // Ensure it doesn't overflow on mobile
    document.body.appendChild(adPopup);
    
    // Add click event to overlay for closing
    adOverlay.addEventListener('click', closePopup);
}

function createMediaElement(ad) {
    if (ad.type === 'video') {
        return `
            <div class="video-container">
                <video class="ad-video" controls autoplay muted>
                    <source src="${ad.mediaUrl}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        `;
    }
    return `<img class="ad-media" src="${ad.mediaUrl}" alt="${ad.title}">`;
}

async function showRandomAd() {
    console.log('Attempting to show random ad...');
    
    const petPreference = await getUserPetPreference();
    
    if (!petPreference) {
        console.error('Could not determine user pet preference');
        return;
    }
    
    console.log(`Showing ad for ${petPreference}`);
    
    // Create elements if they don't exist
    if (!adPopup || !adOverlay) {
        createPopupElements();
    }
    
    const relevantAds = advertisements[petPreference];
    if (!relevantAds || relevantAds.length === 0) {
        console.error(`No advertisements found for preference: ${petPreference}`);
        return;
    }
    
    const ad = relevantAds[Math.floor(Math.random() * relevantAds.length)];
    console.log('Selected ad:', ad);
    
    // Set content
    adPopup.innerHTML = `
        ${createMediaElement(ad)}
        <div class="ad-content">
            <h2>${ad.title}</h2>
            <span class="btn btn-warning" onclick="closePopup()">Close</span>
        </div>
    `;
    
    // Show popup and overlay
    adPopup.style.display = 'block';
    adOverlay.style.display = 'block';
}

function closePopup() {
    if (adPopup && adOverlay) {
        const video = adPopup.querySelector('video');
        if (video) {
            video.pause();
        }
        adPopup.style.display = 'none';
        adOverlay.style.display = 'none';
    }
}

// Make closePopup available globally
window.closePopup = closePopup;

async function initRandomPopup() {
    const petPreference = await getUserPetPreference();
    console.log('Pet Preference:', petPreference);
    
    if (!petPreference) {
        console.error('No pet preference found - ads will not be shown');
        return;
    }
    
    // Create popup elements immediately
    createPopupElements();
    
    // Show first popup after 30 seconds
    setTimeout(() => {
        showRandomAd();
        
        // Then show subsequent popups randomly between 1-3 minutes
        setInterval(() => {
            showRandomAd();
        }, Math.random() * (180000 - 60000) + 60000);
    }, 30000);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initRandomPopup);