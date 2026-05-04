type FormFieldProps = {
  autoComplete?: string;
  error?: string;
  label: string;
  minLength?: number;
  name: string;
  placeholder: string;
  required?: boolean;
  type?: string;
};

export function FormField({
  autoComplete,
  error,
  label,
  minLength,
  name,
  placeholder,
  required = true,
  type = "text",
}: FormFieldProps) {
  return (
    <label className="block">
      <span className="text-sm font-medium text-slate-200">{label}</span>
      <input
        autoComplete={autoComplete}
        className="mt-2 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
        minLength={minLength}
        name={name}
        placeholder={placeholder}
        required={required}
        type={type}
      />
      {error ? <span className="mt-2 block text-xs text-rose-200">{error}</span> : null}
    </label>
  );
}
