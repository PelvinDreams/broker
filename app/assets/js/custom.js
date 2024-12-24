function setItem(key, value) {
  try {
    const encodedValue = encodeData(JSON.stringify(value));
    sessionStorage.setItem(key, encodedValue);
  } catch (e) {
    console.error(e.stack);
  }
}
function getItem(key) {
  try {
    const encodedValue = sessionStorage.getItem(key);
    if (encodedValue) {
      return JSON.parse(decodeData(encodedValue));
    }
    return null;
  } catch (e) {
    console.error(e.stack);
    return null;
  }
}
function encodeData(data) {
  if (typeof btoa === "function") {
    return btoa(data);
  }
  return data;
}
function decodeData(data) {
  if (typeof atob === "function") {
    return atob(data);
  }
  return data;
}
function removeItem(key) {
  try {
    sessionStorage.removeItem(key);
  } catch (e) {
    console.error(e.stack);
  }
}
function clearItems() {
  try {
    sessionStorage.clear();
  } catch (e) {
    console.error(e.stack);
  }
}
