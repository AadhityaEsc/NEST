import { useEffect } from 'react';

export default function MapHardwareManager({ onLocationFound }) {

useEffect(() => {
// 1. Geolocation API: Find student's current location on map load
if ('geolocation' in navigator) {
navigator.geolocation.getCurrentPosition(
(position) => {
// Send coordinates back to parent map component to center the view
onLocationFound(position.coords.latitude, position.coords.longitude);
},
(error) => console.error("Location access denied or failed.")
);
}
}, []);

// 2. Vibration API: Call this function when a user saves a listing or receives a message
const triggerSuccessHaptic = () => {
if ('vibrate' in navigator) {
// Vibrate for 100ms, pause 50ms, vibrate 200ms
navigator.vibrate([100, 50, 200]);
}
};

// 3. Camera API integration for Landlords posting listings
// Attached to an <input type="file" accept="image/*" capture="environment" />
const handleCameraCapture = (event) => {
const photoFile = event.target.files[0];
// Append to FormData and send to Laravel backend via Axios
console.log("Captured image ready for upload:", photoFile.name);
};

return (
<div className="hardware-controls">
    <button onClick={triggerSuccessHaptic}>Test Notification Haptic</button>
    <label>
        Take Room Photo:
        {/* 'capture="environment"' forces mobile devices to open the rear camera directly */}
        <input type="file" accept="image/*" capture="environment" onChange={handleCameraCapture} />
    </label>
</div>
);
}