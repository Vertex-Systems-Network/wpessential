# P-006 Wave 1A — independent evidence integrity review

Status: **REVIEWED, BOUNDED STATIC EVIDENCE ONLY**
Issue: [#947](https://github.com/Vertex-Systems-Network/wpessential/issues/947) (independent Worker); source execution [#945](https://github.com/Vertex-Systems-Network/wpessential/issues/945), parent [#939](https://github.com/Vertex-Systems-Network/wpessential/issues/939)
Review base: `6fd97dc2f8fc1c2a8abe7d1cb0be080f34803c30` (current main at branch claim). Historical Wave 1A is assessed at its original source head, regardless of later P-006 waves.

## Sources and method

I read the merged [PR #946](https://github.com/Vertex-Systems-Network/wpessential/pull/946) metadata and the exact-head [P-006 Actions run 34902018141](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/34902018141), its [job 104169932502](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/34902018141/job/104169932502) step outcomes and full retained job log, [#945's evidence comment](https://github.com/Vertex-Systems-Network/wpessential/issues/945#issuecomment-5671438525), and the workflow/recorder source at `ea1988cfd6aefd0aabdc6703a6f0763d12123c08`. This was read-only review; I did not execute fixtures or rebuild candidates.

PR #946 merged as `4a005b34db7168bf4e31f85450d7b48a53fede0c`. The run is a completed successful `pull_request` run with `head_sha=ea1988cfd6aefd0aabdc6703a6f0763d12123c08`; the job log shows checkout and exact-source verification for that SHA, PHP CLI 8.2.33, both build and comparison steps, the recorder execution, successful upload, and clean tracked source.

## Identity reconciliation

| Item | Independently observed evidence |
| --- | --- |
| Uploaded bundle | Job upload log names artifact `10370662942`, `p006-wave1-static-artifact-evidence-34902018141`, and prints ZIP digest `sha256:763d73755e13eb3f3f21f7c248756f59eaf9bc15ac8b80ef75110712d5a06654`. |
| Free candidate | The logged `sha256sum`, determinism JSON, and emitted evidence JSON agree on `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`. First and second builds match; the job ran `cmp` on their bytes successfully. |
| Pro candidate | The corresponding records agree on `771bf516e9c1d0707a7d036309b14520facf5daa59742b337b0a0fdf622c0363`; first and second builds and byte comparison match. |
| Pair identifier | The exact-head recorder defines SHA-256 over `free:<Free hash>\npro:<Pro hash>\n`. I independently calculated `39a31275225f1b560a22096fcbd66aef8878bc23554c5b32c6861a6a446215ea`, matching the emitted record. This identifier pins candidates; it is not pair certification. |

The upload step lists both ZIPs, both package manifests, determinism JSON, and evidence JSON. **Availability limit:** the Actions run's artifact listing now returns no artifacts (the workflow specified `retention-days: 14`). The historical upload log and issue comment agree, but I could not download the original bundle today or independently rehash its archive and inner candidate bytes. The archive digest above is the upload action's recorded digest, not a fresh download measurement. No mismatch was found in retained evidence; direct archival byte verification remains unavailable.

## Fixture records and scope

The emitted `p006-wave1-evidence.json` printed in the raw job log contains exactly these authorized terminal results, each `status: PASS` with empty `failed_assertions`:

| Fixture | Recorded observation |
| --- | --- |
| FP-01 | Free metadata: version `0.1.0-dev`, Platform API `0.1.0`, schema generation 1, WordPress `6.9`, PHP `8.2`; manifest hash and header/constant checks true. |
| FP-02 | Pro metadata: version `0.1.0-dev`, supported Free `0.1.0-dev…0.1.0-dev`, Platform API `0.1.0…0.1.0`, schema range 1…1 and Pro schema 1; `Requires Plugins: wpessential`; recorded checks true. |
| FP-05 | Free ZIP scan reports 239 entries, `unexpected_payload=[]`, root and bootstrap/payload assertions true. This is the recorder's bounded prohibited-path scan, not a complete semantic proof of absence of any Pro behavior. |
| FP-07 | Free/Pro headers and readme agree on WordPress `6.9` and PHP `8.2`; Composer `>=8.2` parses to floor `8.2`. |
| FP-08 | Source SHA, distinct candidate hashes, manifest comparisons, and two-build equality assertions true; pair ID as above. |

The JSON summary is `executed=5`, `passed=5`, `failed=0`. Against the fixed 144-fixture matrix, the **historical Wave 1A accounting was 5/144 executed**; the other 139 were not executed by this tranche. Later waves have their own evidence and do not expand this review's claim.

The JSON truth boundary says `binary_package_artifact_identity_only=true`; `wordpress_runtime_executed=false`, `database_or_migrations_executed=false`, `remote_provider_or_license_calls_executed=false`, and `production_side_effects=false`. The isolated workflow's visible steps build and inspect disposable ZIPs, with no WordPress runtime or database fixture and no provider/license invocation. These negative values describe this workflow's scope; they are not evidence for unrun environments.

## Conclusion and integration requirement

The retained run, job log, exact-head recorder, and #945 report agree on the five bounded static PASS results and identifiers, subject to the expired artifact availability limit. **5/144 executed does not imply Free/Pro pair certification, WordPress runtime certification, ADR-0010 acceptance, or promotion of adjacent platform API, schema, entitlement, account/allocation, membership, or update-trust evidence domains.** The emitted summary itself records `p006_runtime_certified=false`, `free_pro_pair_certified=false`, and `adjacent_certifications_promoted=[]`. No certification, deploy, release, or fixture execution is performed by this review.

No evidence discrepancy requiring a defect link to #939/#945 was identified; the unrepeatable direct archive-byte check is disclosed above. This one-file Worker PR should be reviewed and merged under normal applicable exact-head gates before #947 is closed. Supervisor integration should reconcile the concise README progress and compact state, without treating this historical review as new P-006 fixture execution or changing the 56-surface delivery dashboard.
