import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

const API_BASE = 'http://localhost/api';

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    const saved = localStorage.getItem('kst_user');
    return saved ? JSON.parse(saved) : null;
  });

  useEffect(() => {
    if (user) {
      localStorage.setItem('kst_user', JSON.stringify(user));
    } else {
      localStorage.removeItem('kst_user');
    }
  }, [user]);

  const login = async (email, password) => {
    const res = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    const json = await res.json();

    if (!res.ok || !json.success) {
      // Backend kirim 'message' kalo gagal
      throw new Error(json.message || 'Login gagal. Coba lagi.');
    }

    // Response sukses: { success: true, data: { token, user: { id, name, email, role, status } } }
    const userData = {
      id: json.data.user.id,
      name: json.data.user.name,
      email: json.data.user.email,
      role: json.data.user.role,   // 'admin' atau 'operator'
      status: json.data.user.status,
      token: json.data.token,       // JWT buat dipake di header semua request
    };

    setUser(userData);
    return userData;
  };

  const logout = async () => {
    // Optional: hit endpoint logout buat invalidate token di server
    if (user?.token) {
      try {
        await fetch(`${API_BASE}/auth/logout`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${user.token}`,
            'Accept': 'application/json',
          },
        });
      } catch (_) {
        // Kalo gagal pun, tetep clear local state
      }
    }
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, login, logout, isAuthenticated: !!user }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}