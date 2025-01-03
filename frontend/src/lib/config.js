export const config = {
  apiUrl: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000',
  storageUrl: `${import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'}/storage`,
};

export const getStorageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `${config.storageUrl}/${path}`;
}; 