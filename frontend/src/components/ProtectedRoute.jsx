import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function ProtectedRoute({ children, allowedRoles }) {
  const { user, isAuthenticated } = useAuth();

  // Belum login → redirect ke login
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  // Login tapi role gak sesuai → redirect ke dashboard
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/dashboard" replace />;
  }

  return children;
}