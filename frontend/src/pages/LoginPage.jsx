import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Leaf, Mail, Lock, Eye, EyeOff, AlertCircle, ArrowRight } from 'lucide-react';
import { useLocation } from 'react-router-dom';

export default function LoginPage() {
  const navigate = useNavigate();
  const { login } = useAuth();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const location = useLocation();
  const isSessionExpired = location.state?.reason === 'session_expired';

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const userData = await login(email, password);
      // Redirect ke dashboard setelah login sukses
      navigate('/dashboard', { replace: true });
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex">
      {/* ====== LEFT PANEL — Branding ====== */}
      <div className="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-[#1a2e1a] via-[#1f3d1f] to-[#0f1f0f] flex-col justify-between p-12 relative overflow-hidden">
        {/* Background Pattern */}
        <div className="absolute inset-0 opacity-5"
          style={{
            backgroundImage: 'radial-gradient(circle at 2px 2px, white 1px, transparent 0)',
            backgroundSize: '40px 40px',
          }}
        />

        {/* Top — Logo */}
        <div className="relative z-10">
          <div className="flex items-center gap-3 mb-16">
            <div className="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
              <Leaf size={22} className="text-white" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-white">KST Ngijo</h2>
              <p className="text-xs text-emerald-400">Green Science Park</p>
            </div>
          </div>

          <h1 className="text-4xl font-bold text-white leading-tight mb-4">
            Pioneering Green Science,
            <br />
            <span className="text-emerald-400">Empowering the Community</span>
          </h1>
          <p className="text-gray-400 text-sm leading-relaxed max-w-md">
            Sistem informasi terintegrasi untuk monitoring, penelitian, dan pengelolaan
            kawasan sains hijau KST Ngijo, Kota Malang.
          </p>
        </div>

        {/* Bottom — Stats */}
        <div className="relative z-10 flex gap-8">
          {[
            { value: '142', label: 'Proyek Aktif' },
            { value: '384', label: 'Total Paten' },
            { value: '92', label: 'Green Score' },
          ].map((stat, i) => (
            <div key={i}>
              <p className="text-2xl font-bold text-emerald-400">{stat.value}</p>
              <p className="text-xs text-gray-500">{stat.label}</p>
            </div>
          ))}
        </div>

        {/* Decorative glow */}
        <div className="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl" />
        <div className="absolute -top-20 -left-20 w-72 h-72 bg-emerald-600/5 rounded-full blur-3xl" />
      </div>

      {isSessionExpired && (
        <div className="flex items-center gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl mb-4">
          <AlertCircle size={18} className="text-amber-500 flex-shrink-0" />
          <p className="text-sm text-amber-700 font-medium">Sesi kamu sudah habis. Silakan login kembali.</p>
        </div>
      )}

      {/* ====== RIGHT PANEL — Login Form ====== */}
      <div className="flex-1 flex items-center justify-center px-8 bg-[#f5f5f0]">
        <div className="w-full max-w-md">
          {/* Mobile Logo */}
          <div className="lg:hidden flex items-center gap-3 mb-10">
            <div className="w-10 h-10 bg-emerald-700 rounded-xl flex items-center justify-center">
              <Leaf size={22} className="text-white" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-gray-900">KST Ngijo</h2>
              <p className="text-xs text-gray-400">Green Science Park</p>
            </div>
          </div>

          <h2 className="text-2xl font-bold text-gray-900 mb-1">Masuk ke Portal</h2>
          <p className="text-sm text-gray-500 mb-8">
            Gunakan kredensial yang diberikan oleh administrator.
          </p>

          {/* Error Banner */}
          {error && (
            <div className="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-6">
              <AlertCircle size={18} className="text-red-500 flex-shrink-0" />
              <p className="text-sm text-red-700">{error}</p>
            </div>
          )}

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-5">
            {/* Email */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="nama@kst.local"
                  className="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
              </div>
            </div>

            {/* Password */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                <input
                  type={showPassword ? 'text' : 'password'}
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Masukkan password"
                  className="w-full pl-10 pr-12 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                >
                  {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            {/* Submit */}
            <button
              type="submit"
              disabled={loading}
              className="w-full flex items-center justify-center gap-2 py-3 bg-emerald-700 text-white rounded-xl text-sm font-semibold hover:bg-emerald-800 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? (
                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
              ) : (
                <>
                  Masuk
                  <ArrowRight size={16} />
                </>
              )}
            </button>
          </form>

          <p className="text-center text-xs text-gray-400 mt-8">
            © 2026 KST Ngijo Green Science Park. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  );
}