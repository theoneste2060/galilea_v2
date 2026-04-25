import { query, mutation } from "./_generated/server";

export const getFeed = query({
  args: {},
  handler: async (ctx) => {
    const posts = await ctx.db.query("posts").collect();
    return posts.sort((a: any, b: any) => b.createdAt - a.createdAt);
  },
});

export const createPost = mutation({
  args: {},
  handler: async (ctx, args: any) => {
    const { content, userId } = args;
    await ctx.db.insert("posts", {
      content,
      userId,
      createdAt: Date.now(),
    });
  },
});