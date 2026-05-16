import { ui } from "@/components/ui/styles";

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
      <span className={ui.fieldLabel}>{label}</span>
      <input
        autoComplete={autoComplete}
        className={`mt-2 ${ui.field}`}
        minLength={minLength}
        name={name}
        placeholder={placeholder}
        required={required}
        type={type}
      />
      {error ? <span className={ui.fieldError}>{error}</span> : null}
    </label>
  );
}
