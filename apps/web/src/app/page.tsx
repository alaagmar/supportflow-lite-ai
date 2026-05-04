export default function Home() {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8080";

  return (
    <main className="min-h-screen bg-slate-950 px-6 py-10 text-white sm:px-10 lg:px-16">
      <section className="mx-auto flex min-h-[calc(100vh-5rem)] max-w-5xl flex-col justify-center">
        <div className="mb-8 inline-flex w-fit rounded-full border border-cyan-300/25 bg-cyan-300/10 px-4 py-2 text-sm font-medium text-cyan-100">
          Next.js dashboard scaffold ready
        </div>

        <h1 className="max-w-3xl text-4xl font-semibold tracking-tight sm:text-6xl">
          SupportFlow Lite AI
        </h1>

        <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
          Multi-tenant AI support triage with queued classification, policy
          retrieval, draft replies, and human review.
        </p>

        <div className="mt-10 grid gap-4 sm:grid-cols-3">
          {[
            ["Frontend", "Next.js 15 App Router"],
            ["Backend", "Laravel 12 API"],
            ["API URL", apiUrl],
          ].map(([label, value]) => (
            <div
              className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20"
              key={label}
            >
              <p className="text-sm text-slate-400">{label}</p>
              <p className="mt-2 font-medium text-slate-50">{value}</p>
            </div>
          ))}
        </div>
      </section>
    </main>
  );
}
