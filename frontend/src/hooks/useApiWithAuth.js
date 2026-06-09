import { useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { apiFetch } from '../utils/apiFetch';

// Hook ini wraps apiFetch + auto-handle token expired
export function useApiWithAuth() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const fetchWithAuth = useCallback(
    async (endpoint, options = {}) => {
      try {
        return await apiFetch(endpoint, options, user?.token);
      } catch (err) {
        if (err.status === 401 || err.message === 'SESSION_EXPIRED') {
          logout();
          navigate('/login', { state: { reason: 'session_expired' } });
          return null;
        }
        throw err;
      }
    },
    [user?.token, logout, navigate]
  );

  return { fetchWithAuth };
}