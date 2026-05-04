"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import {
  apiRequest,
  ApiRequestError,
  type AuthSessionPayload,
  type WorkspacePayload,
} from "@/lib/api";
import { clearAuthSession, getAuthPortal, getAuthToken, setAuthSession } from "@/lib/session";

export type FormState = {
  message?: string;
  errors?: Record<string, string[]>;
};

export async function ownerLoginAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  return loginForPortal(formData, "owner", "/owner/workspaces");
}

export async function staffLoginAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  return loginForPortal(formData, "staff", "/staff/dashboard");
}

export async function ownerRegisterAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  let session: AuthSessionPayload;

  try {
    session = await apiRequest<AuthSessionPayload>("/api/owner/auth/register", {
      body: JSON.stringify({
        name: valueOf(formData, "name"),
        email: valueOf(formData, "email"),
        password: valueOf(formData, "password"),
        password_confirmation: valueOf(formData, "password_confirmation"),
        workspace_name: valueOf(formData, "workspace_name"),
      }),
      method: "POST",
    });
  } catch (error) {
    return formError(error);
  }

  await setAuthSession(session.data.token, session.data.portal);
  redirect("/owner/workspaces");
}

export async function createWorkspaceAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const token = await getAuthToken();

  if (!token) {
    redirect("/owner/login");
  }

  try {
    await apiRequest<WorkspacePayload>("/api/owner/workspaces", {
      body: JSON.stringify({ name: valueOf(formData, "name") }),
      method: "POST",
      token,
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath("/owner/workspaces");

  return { message: "Workspace created." };
}

export async function logoutAction(): Promise<void> {
  const token = await getAuthToken();
  const portal = await getAuthPortal();

  if (token && portal) {
    await apiRequest<void>(`/api/${portal}/auth/logout`, {
      method: "POST",
      token,
    }).catch(() => undefined);
  }

  await clearAuthSession();
  redirect("/");
}

async function loginForPortal(
  formData: FormData,
  portal: "owner" | "staff",
  redirectTo: string,
): Promise<FormState> {
  let session: AuthSessionPayload;

  try {
    session = await apiRequest<AuthSessionPayload>(`/api/${portal}/auth/login`, {
      body: JSON.stringify({
        email: valueOf(formData, "email"),
        password: valueOf(formData, "password"),
      }),
      method: "POST",
    });
  } catch (error) {
    return formError(error);
  }

  await setAuthSession(session.data.token, session.data.portal);
  redirect(redirectTo);
}

function valueOf(formData: FormData, key: string): string {
  const value = formData.get(key);

  return typeof value === "string" ? value : "";
}

function formError(error: unknown): FormState {
  if (error instanceof ApiRequestError) {
    return {
      errors: error.errors,
      message: error.message,
    };
  }

  return {
    message: "Unable to reach the SupportFlow API. Check the dev stack and try again.",
  };
}
