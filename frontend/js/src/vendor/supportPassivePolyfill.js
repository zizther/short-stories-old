// Test via a getter in the options object to see if the passive property is accessed
var supportsPassive = false;

try {
    var opts = Object.defineProperty({}, 'passive', {
        get: function() {
            supportsPassive = true;
        }
    });
    window.addEventListener("testPassive", null, opts);
    window.removeEventListener("testPassive", null, opts);
} catch (e) {}
