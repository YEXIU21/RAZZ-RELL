export const config = {
  apiUrl: import.meta.env.VITE_API_URL || 'https://razz-rell.onrender.com',
  storageUrl: `${import.meta.env.VITE_API_URL || 'https://razz-rell.onrender.com'}/storage`,
  socketUrl: import.meta.env.VITE_SOCKET_URL || 'https://razz-rell.onrender.com',
};

export const getStorageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `${config.storageUrl}/${path}`;
}; 