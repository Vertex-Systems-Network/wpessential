# ADR-0192 — Media Performance, Responsive Delivery & Field Optimization Expansion

Status: **Accepted planning architecture / evidence pending / no development authorization**  
Date: 2026-08-29

## Context

The owner requested audits of Image Prioritizer and Auto Sizes / Enhanced Responsive Images and asked that WPE image handling become advanced and competitive.

WPE already has Surface 28 for media/watermark derivative rules. Creating a second image module would duplicate transformation, derivative, cache, offload and media-ownership concerns. The correct architecture is to expand Surface 28 into a coherent media transformation + responsive-delivery + performance-intelligence surface.

## Decision

Accept `docs/MODULES/MEDIA-PERFORMANCE-DELIVERY-EXPANSION.md` as an authoritative addendum to Surface 28.

Add product behavior for:
- WordPress/Core capability detection and no duplicate override of Core-owned features;
- privacy-aware field/URL metrics for media-performance decisions;
- viewport evidence profiles;
- LCP-aware `fetchpriority`, lazy/eager and responsive preload decisions;
- removal of unjustified priority when certified evidence contradicts heuristic priority;
- occluded initial-viewport media handling;
- `sizes=auto`/responsive-size intelligence with Core-aware ownership;
- Picture/source/art-direction diagnostics;
- bounded background-image and video-poster optimization profiles;
- AVIF/WebP/fallback derivative policies;
- dominant-color/placeholder profiles;
- dimensions/CLS diagnostics;
- CDN/offload/cache integration;
- route/template diagnostics and regeneration;
- coexistence with WordPress Performance Team plugins/Core features.

## Existing invariant preserved

Standard WPE media processing does not mutate the canonical original source file in place. Existing `WM` derivative/watermark evidence remains separately authoritative.

## Evidence

Reserve **MDP-001…MDP-176**, executed **0/176**.

Existing `WM-01…WM-176` remains unexecuted and separate.

## Development gate

No RUM collection, image preload, fetchpriority/lazy rewrite, AVIF/WebP generation, placeholder generation, CDN action, derivative regeneration, source replacement, browser measurement or runtime test is authorized by this ADR.