import { defineSchema, defineTable } from "convex/server";
import { v } from "convex/values";

export default defineSchema({
  users: defineTable({
    clerkId: v.string(),
    username: v.string(),
    createdAt: v.number(),
  }),
  posts: defineTable({
    userId: v.string(),
    content: v.string(),
    createdAt: v.number(),
  }),
});