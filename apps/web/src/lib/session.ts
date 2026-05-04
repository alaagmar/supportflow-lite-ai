import { cookies } from "next/headers";

export const AUTH_TOKEN_COOKIE = "supportflow_token";
export const PORTAL_COOKIE = "supportflow_portal";

const cookieOptions = {
  httpOnly: true,
  maxAge: 60 * 60 * 8,
  path: "/",
  sameSite: "lax" as const,
  secure: process.env.NODE_ENV === "production",
};

export async function getAuthToken(): Promise<string | undefined> {
  const cookieStore = await cookies();

  return cookieStore.get(AUTH_TOKEN_COOKIE)?.value;
}

export async function getAuthPortal(): Promise<"owner" | "admin" | "staff" | undefined> {
  const cookieStore = await cookies();
  const portal = cookieStore.get(PORTAL_COOKIE)?.value;

  return portal === "owner" || portal === "admin" || portal === "staff"
    ? portal
    : undefined;
}

export async function setAuthSession(
  token: string,
  portal: "owner" | "admin" | "staff",
): Promise<void> {
  const cookieStore = await cookies();

  cookieStore.set(AUTH_TOKEN_COOKIE, token, cookieOptions);
  cookieStore.set(PORTAL_COOKIE, portal, cookieOptions);
}

export async function clearAuthSession(): Promise<void> {
  const cookieStore = await cookies();

  cookieStore.delete(AUTH_TOKEN_COOKIE);
  cookieStore.delete(PORTAL_COOKIE);
}
