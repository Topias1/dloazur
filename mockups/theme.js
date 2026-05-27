/* Tailwind Play CDN config — shared across every mockup.
   Loaded synchronously right after the CDN <script>, so the
   config is set before Tailwind's first DOM scan.
   Colors mirror the OKLCH tokens in app.css. */
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        display: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        azure: {
          50:  'oklch(0.972 0.018 250)',
          100: 'oklch(0.940 0.038 250)',
          200: 'oklch(0.885 0.070 250)',
          300: 'oklch(0.805 0.110 251)',
          400: 'oklch(0.705 0.158 252)',
          500: 'oklch(0.620 0.190 253)',
          600: 'oklch(0.548 0.188 255)',
          700: 'oklch(0.470 0.160 257)',
          800: 'oklch(0.395 0.128 259)',
          900: 'oklch(0.320 0.098 261)',
          950: 'oklch(0.240 0.066 263)',
        },
        lagon: {
          300: 'oklch(0.860 0.110 190)',
          400: 'oklch(0.790 0.130 189)',
          500: 'oklch(0.710 0.128 189)',
          600: 'oklch(0.610 0.112 191)',
        },
        coral: {
          400: 'oklch(0.770 0.150 47)',
          500: 'oklch(0.705 0.175 44)',
        },
        sand: {
          50:  'oklch(0.988 0.006 85)',
          100: 'oklch(0.970 0.009 84)',
          200: 'oklch(0.928 0.012 83)',
          300: 'oklch(0.880 0.014 82)',
        },
        ink: {
          950: 'oklch(0.230 0.040 258)',
          900: 'oklch(0.300 0.052 257)',
          700: 'oklch(0.440 0.034 256)',
          500: 'oklch(0.585 0.026 255)',
          400: 'oklch(0.690 0.020 255)',
        },
        success: 'oklch(0.700 0.150 155)',
        warn:    'oklch(0.800 0.130 80)',
        danger:  'oklch(0.620 0.210 25)',
      },
      boxShadow: {
        xs: '0 1px 2px oklch(0.30 0.06 256 / 0.06)',
        sm: '0 1px 2px oklch(0.30 0.06 256 / 0.05), 0 4px 12px -6px oklch(0.30 0.06 256 / 0.10)',
        md: '0 2px 4px oklch(0.30 0.06 256 / 0.05), 0 12px 28px -10px oklch(0.30 0.06 256 / 0.16)',
        lg: '0 4px 8px oklch(0.30 0.06 256 / 0.06), 0 28px 56px -16px oklch(0.30 0.06 256 / 0.22)',
      },
      borderRadius: {
        xl: '0.875rem',
        '2xl': '1.25rem',
        '3xl': '1.75rem',
      },
      maxWidth: { content: '72rem' },
      transitionTimingFunction: {
        'out-quint': 'cubic-bezier(0.22, 1, 0.36, 1)',
      },
    },
  },
};
