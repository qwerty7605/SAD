export const environment = {
  production: false,
  apiUrl: ''  // Will be set dynamically
};

// Helper function to get API URL based on current hostname
export function getApiUrl(): string {
  const hostname = window.location.hostname;
  return `http://${hostname}`;
}
