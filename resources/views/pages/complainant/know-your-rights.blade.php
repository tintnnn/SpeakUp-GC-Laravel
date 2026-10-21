<x-layouts.complainant>
@php
    $allowedTabs = ['laws', 'reporting', 'faq', 'wellness', 'policy'];
    $tab = request('tab', 'laws');
    if (! in_array($tab, $allowedTabs, true)) {
        $tab = 'laws';
    }
    $reviewed = 'May 2026';

    $acts = [
        [
            'acronym' => 'Safe Spaces Act',
            'raNumber' => 'RA 11313',
            'icon' => 'shield-check',
            'summary' => 'Enacted in 2019, the Safe Spaces Act expands the definition of sexual harassment to cover gender-based harassment in streets, public spaces, online, workplaces, and educational institutions. It covers all genders including women, men, and LGBTQIA+ persons.',
            'who' => 'All persons regardless of sex, gender, or sexual orientation — including students, faculty, staff, and visitors in educational settings.',
            'penalties' => '1–6 months imprisonment and/or fines of ₱10,000–₱500,000 depending on the offense. Institutions face suspension or closure for non-compliance.',
            'provisions' => [
                'Prohibits gender-based sexual harassment in streets, public spaces, online, workplaces, and schools.',
                'Covers catcalling, wolf-whistling, unwanted sexual remarks, unsolicited sexual invitations, misogynistic and homophobic slurs.',
                'Schools must adopt a Code of Conduct and create mechanisms to receive, investigate, and act on complaints.',
                'Mandates at least one (1) anti-sexual harassment focal person per institution.',
                'Prohibits retaliation against complainants and witnesses.',
                'Online sexual harassment includes offensive communications, uploading content without consent, and cyberstalking.',
            ],
        ],
        [
            'acronym' => 'Anti-Sexual Harassment Act',
            'raNumber' => 'RA 7877',
            'icon' => 'ban',
            'summary' => 'The Anti-Sexual Harassment Act of 1995 was the first Philippine law addressing sexual harassment in work and educational environments. It holds employers and educators directly liable for failing to prevent or address harassment.',
            'who' => 'Employees, students, and trainees subjected to sexual harassment by a person with authority, influence, or moral ascendancy over them.',
            'penalties' => 'Imprisonment of 1–6 months and/or fine of ₱10,000–₱20,000. Administrative sanctions include dismissal, suspension, or demotion.',
            'provisions' => [
                'Defines sexual harassment as an act committed by a person in a position of authority, influence, or moral ascendancy.',
                'Covers workplaces, educational institutions, and training environments.',
                'Requires all institutions to create a written anti-sexual harassment policy.',
                'Mandates the creation of a Committee on Decorum and Investigation (CODI).',
                'Expressly holds superior officers liable if they fail to act on known incidents.',
                'Complainant may file both criminal and administrative cases simultaneously.',
            ],
        ],
        [
            'acronym' => 'Data Privacy Act',
            'raNumber' => 'RA 10173',
            'icon' => 'lock',
            'summary' => 'The Data Privacy Act protects the privacy and security of personal information. In GBV complaints, it ensures your identity and case details remain strictly confidential.',
            'who' => 'All persons whose personal data is collected, including complainants, respondents, and witnesses in any complaint proceeding.',
            'penalties' => 'Imprisonment of 1–6 years and fines of ₱500,000–₱4,000,000 for unauthorized processing, access, or disclosure of personal information.',
            'provisions' => [
                'Personal data must be processed lawfully and only for its stated purpose.',
                'Data collected must be proportionate — only what is necessary for the complaint.',
                'Complainants have the right to access, correct, and request erasure of their data.',
                'Data must be protected from unauthorized access through adequate security measures.',
                "Unauthorized disclosure of a complainant's identity is a criminal offense.",
                'The National Privacy Commission (NPC) oversees compliance and receives complaints.',
            ],
        ],
    ];

    $steps = [
        ['step' => 1, 'title' => 'Recognize the Incident', 'description' => 'Identify what happened. You do not need to be certain about the law — the DEIU will help classify the incident. Trust your instincts.', 'tips' => ['Trust your instincts — if something felt wrong, it likely was.', 'You are not required to have physical evidence to file a complaint.', 'An incident can be reported even if it happened some time ago.', "Anonymous reporting is available if you're unsure about disclosing your identity."]],
        ['step' => 2, 'title' => 'Secure Your Safety', 'description' => 'Your immediate safety is the priority. If you are in danger, contact emergency services first. Then document the incident privately — date, time, location, description, and witnesses.', 'tips' => ['Take screenshots of digital evidence (messages, posts, emails) and store them securely.', 'Write down what happened in your own words while it is fresh.', 'Identify any witnesses who may support your account.', 'Do not confront the respondent directly if you feel unsafe.']],
        ['step' => 3, 'title' => 'File a Complaint on SpeakUp GC', 'description' => 'Use "File a Complaint" to submit a formal or anonymous complaint. All submitted reports are kept confidential. A written sworn statement (sinumpaang salaysay) is required for formal complaints.', 'tips' => ['You may remain anonymous to the respondent and still share a sealed identity with DEIU for fairness.', 'Upload all available evidence: photos, screenshots, documents, recordings.', 'A sworn statement strengthens your complaint and is required for formal cases.', 'You will receive a Case ID immediately — track acceptance and every move in My Cases.']],
        ['step' => 4, 'title' => 'DEIU Review & Investigation', 'description' => 'The DEIU will review your complaint, a CODI member will accept it, and a formal investigation will be conducted in accordance with the Gordon College CODI and applicable Republic Acts.', 'tips' => ['The tracker moves to Accepted when a CODI member takes your case — that is how you know it is moving.', 'You may be asked to provide additional evidence or a formal statement.', 'The DEIU will contact the respondent separately — your identity is protected.', 'Both parties have the right to be heard. Retaliation is strictly prohibited.']],
        ['step' => 5, 'title' => 'Formal Hearing (if applicable)', 'description' => 'For formal complaints, a hearing may be scheduled where both parties can present their case. You will be notified of the date, time, and venue.', 'tips' => ['You will receive written notice at least 5 days before the hearing.', 'You have the right to bring a support person, advocate, or representative.', 'You may submit written statements if you cannot attend in person.', 'The hearing is conducted in a safe, confidential environment.']],
        ['step' => 6, 'title' => 'Decision & Next Steps', 'description' => 'The DEIU will issue a written decision. Both parties will be notified of the outcome. You may file a motion for reconsideration within the prescribed period.', 'tips' => ['The written decision includes findings, conclusions, and any sanction imposed.', 'You have the right to appeal or file a motion for reconsideration.', 'After the case is resolved, you can rate how the complaint was handled.', 'You may also pursue civil or criminal remedies independently through the courts.']],
    ];

    $faqs = [
        ['q' => 'Can I file a complaint anonymously?', 'a' => 'Yes. SpeakUp GC supports anonymous filing — the respondent will not see your name. For fairness, you may also share a sealed identity with DEIU only so investigators can contact you and process both sides fairly if the case goes to formal investigation. Fully anonymous complaints are still reviewed, but follow-up may be limited.'],
        ['q' => 'Are submitted reports kept confidential?', 'a' => 'Yes. All reports submitted through SpeakUp GC are kept strictly confidential. Only authorized DEIU and CODI personnel can access your case. Your identity is never disclosed to the respondent without your consent, in line with the Data Privacy Act (RA 10173).'],
        ['q' => 'What happens after I submit a complaint?', 'a' => 'You receive a Case ID immediately. DEIU reviews the report, then a CODI member accepts it from the queue. Track each move in My Cases: Submitted → Accepted → Investigating → Decision. You are notified when the complaint is accepted and whenever the status changes.'],
        ['q' => 'How do I know my complaint was accepted and is moving?', 'a' => 'Open My Cases or the complaint tracker. When a CODI member takes your case, the tracker moves to Accepted and you receive a notification. Messaging opens after acceptance. If there is no movement after a few days, you can request a follow-up from the case page.'],
        ['q' => 'Can I rate how my complaint was handled?', 'a' => 'Yes. Once your case is resolved or closed, you can rate the handling (1–5 stars) and leave a short comment. This helps DEIU see whether the complaint system did its job. Your rating is confidential and is not shown to the respondent.'],
        ['q' => 'Will the respondent know who filed the complaint?', 'a' => 'Your identity is strictly confidential and will NOT be disclosed to the respondent without your explicit written consent. The DEIU is bound by the Data Privacy Act (RA 10173) and institutional policy to protect complainant identities at all times.'],
        ['q' => 'What counts as sexual harassment under Philippine law?', 'a' => 'Under RA 11313 and RA 7877, sexual harassment includes: unwanted physical contact, sexual remarks or jokes, catcalling, wolf-whistling, displaying sexual materials, unsolicited sexual invitations, sending explicit messages or images, cyberstalking, misogynistic or homophobic slurs, and any act that demeans, intimidates, or endangers a person based on their sex or gender.'],
        ['q' => 'What if the harasser is a professor or person of authority?', 'a' => 'This is explicitly covered by RA 7877. Persons in authority — including professors, supervisors, trainers, and administrators — are held to a higher standard. Filing a complaint against a superior is protected, and retaliation (such as failing grades or hostility) is itself a punishable offense.'],
        ['q' => 'Is there a deadline for filing a complaint?', 'a' => 'For administrative complaints within Gordon College, the CODI prescribes specific prescription periods. Under RA 11313, a criminal complaint must be filed within the prescriptive period (generally 1–10 years depending on severity). File as soon as possible while evidence and memories are fresh, but late filing does not automatically disqualify a complaint.'],
        ['q' => 'Can I file a complaint if the harassment happened online?', 'a' => 'Yes. RA 11313 explicitly covers online and digital spaces. Gender-based online sexual harassment includes sending unwanted explicit content, cyberstalking, creating demeaning content, and posting intimate images without consent. Screenshot and preserve all digital evidence before filing.'],
        ['q' => "What is a 'sinumpaang salaysay' and do I need one?", 'a' => 'A sinumpaang salaysay is a written account of events sworn under oath before a notary public or authorized official. It is required for formal complaints as it establishes the factual basis of your case and carries legal weight. Write it in your own words — the DEIU can guide you through the process.'],
        ['q' => 'Can the respondent retaliate against me for filing a complaint?', 'a' => 'Retaliation is strictly prohibited and is itself a punishable offense under RA 11313 and the Gordon College CODI. If you experience any form of retaliation — threats, harassment, discrimination, or changes in academic standing — report it immediately to the DEIU.'],
        ['q' => 'What support is available while my case is ongoing?', 'a' => 'Gordon College DEIU offers counseling, academic accommodations, safety planning, and referral services throughout the complaint process. You are not alone, and you do not need to wait for a case resolution to access support.'],
        ['q' => 'Can I pursue both an institutional and a criminal complaint at the same time?', 'a' => 'Yes. Filing an institutional complaint with the DEIU does not prevent you from simultaneously filing a criminal complaint with the PNP or a civil case in court. Both processes are independent of each other, and the DEIU can assist in documenting evidence that may support a legal case.'],
    ];

    $wellness = [
        ['icon' => 'heart', 'title' => 'Psychological First Aid', 'description' => 'If you just experienced an incident, grounding yourself is important. Focus on your breathing. You are safe right now. The DEIU and Guidance Office can provide immediate psychological support.'],
        ['icon' => 'users', 'title' => 'Peer Support & Community', 'description' => "You are not alone. Many survivors find strength in connecting with others. Gordon College's wellness programs offer peer support circles and group sessions for students affected by GBV."],
        ['icon' => 'book-open', 'title' => 'Self-Care & Coping Resources', 'description' => 'Healing is non-linear. Journaling, physical activity, creative expression, and mindfulness can help manage stress and trauma responses. The Guidance Office can recommend programs tailored to your needs.'],
        ['icon' => 'eye', 'title' => 'Safety Planning', 'description' => 'If you are concerned about your ongoing safety — especially involving someone on campus — the DEIU and Guidance Office can work with you to create a confidential safety plan.'],
        ['icon' => 'handshake', 'title' => 'Academic Accommodations', 'description' => 'If a GBV incident is affecting your studies, you may request academic accommodations including deadline extensions, section transfers, or modified attendance. Contact the DEIU with your request.'],
        ['icon' => 'lock', 'title' => 'Your Rights During Recovery', 'description' => 'You have the right to recover at your own pace. You are not obligated to disclose your experience publicly. You may withdraw from the formal complaint process without affecting your access to support.'],
    ];

    $policies = [
        [
            'icon' => 'gavel',
            'subtitle' => 'Established under RA 7877 & RA 11313',
            'title' => 'Gordon College Committee on Decorum and Investigation (GC-CODI)',
            'description' => 'The GC-CODI is mandated to investigate complaints of sexual harassment and gender-based sexual harassment, ensuring a safe and respectful environment for all members of the Gordon College community.',
            'details' => [
                'Chaired by VP for IPDEA with representatives from faculty, students, HRMU, and DEI Unit.',
                'Receives and investigates all sexual and gender-based sexual harassment complaints.',
                'Submits investigation report within 10 days to the Disciplining Authority for decision.',
                'Protects complainants from retaliation and ensures confidentiality throughout the process.',
                'Conducts educational programs to prevent incidents of harassment.',
                'Follows due process — both parties have the right to be heard and present evidence.',
            ],
        ],
        [
            'icon' => 'ban',
            'subtitle' => 'Three-tier offense classification system',
            'title' => 'Sanctions and Disciplinary Actions',
            'description' => 'GC-CODI implements a graduated disciplinary system based on the gravity of the offense. All sanctions comply with RA 7877, RA 11313, and CHED/CSC memoranda.',
            'details' => [
                'GRAVE OFFENSES: Unwanted touching of private parts, sexual assault, requesting sexual favors in exchange for benefits. Penalty: Dismissal (subject to Board of Trustees resolution).',
                'LESS GRAVE OFFENSES: Unwanted touching/brushing, derogatory remarks, verbal abuse with sexual overtones. Penalty: 1 month to 6 months suspension (1st offense), dismissal (2nd offense).',
                'LIGHT OFFENSES: Sexist statements, malicious leering, unwelcome sexual comments, offensive gestures. Penalty: Reprimand (1st), suspension 1-30 days (2nd), dismissal (3rd).',
                'Online sexual harassment includes unwanted messages, cyberstalking, uploading content without consent, and impersonation.',
                'Street harassment includes catcalling, wolf-whistling, unwanted invitations, and public indecency.',
            ],
        ],
        [
            'icon' => 'users',
            'subtitle' => 'Clear procedures and timelines',
            'title' => 'Complaint Filing & Investigation Process',
            'description' => 'The GC-CODI follows strict procedural guidelines to ensure transparency, fairness, and efficiency in handling all complaints.',
            'details' => [
                'Pre-filing assistance: Counseling, referral to professional help, and guidance on filing options.',
                'Written, signed, and sworn complaint required with: complainant details, respondent information, statement of facts, evidence, and certification of non-forum shopping.',
                'Preliminary investigation commences within 5 days and concludes within 15 working days.',
                'Respondent has 3 days to submit Counter-Affidavit after receiving notice.',
                'Formal charge issued within 3 days if prima facie case is established.',
                'Anonymous complaints are accepted. A sealed identity for DEIU only is recommended for fairness so both sides can be processed if the case proceeds — still never shown to the respondent.',
            ],
        ],
        [
            'icon' => 'shield-check',
            'subtitle' => 'Safeguarding complainants and witnesses',
            'title' => 'Protection Orders & Anti-Retaliation',
            'description' => 'GC-CODI prioritizes the safety and well-being of complainants, witnesses, and all parties involved in the investigation process.',
            'details' => [
                'Protection orders may be issued to prevent further harm or threats from perpetrators.',
                'Retaliation against complainants or witnesses is strictly prohibited and independently actionable.',
                'Academic accommodations available: deadline extensions, section transfers, modified attendance.',
                'Confidentiality maintained at all stages — identities protected under Data Privacy Act (RA 10173).',
                'Complainants may withdraw from formal complaint process without affecting access to support services.',
                'DEIU and Guidance Office provide psychological support, safety planning, and referral services.',
            ],
        ],
        [
            'icon' => 'lock',
            'subtitle' => 'In compliance with RA 10173 — Data Privacy Act',
            'title' => 'Data Privacy & Confidentiality Policy',
            'description' => 'Gordon College handles all complaint data in compliance with the Data Privacy Act (RA 10173). This policy governs how your personal information is collected, stored, accessed, and protected throughout the complaint process.',
            'details' => [
                'Only authorized GC-CODI and DEIU personnel have access to complaint records and personal data.',
                'Complainant identity will NOT be disclosed to the respondent or any third party without explicit written consent.',
                'Complaint records stored securely and retained for minimum of 10 years as required by law.',
                'Unauthorized disclosure of personal information violates RA 10173 and institutional policy.',
                'You have the right to access, correct, and request erasure of your personal data upon written request.',
                'All proceedings, documents, and identities kept strictly confidential to protect rights and dignity of all individuals.',
            ],
        ],
    ];

    $tabs = [
        ['id' => 'laws', 'label' => 'Legal Rights', 'icon' => 'scale'],
        ['id' => 'reporting', 'label' => 'How to Report', 'icon' => 'file-text'],
        ['id' => 'faq', 'label' => 'FAQs', 'icon' => 'help-circle'],
        ['id' => 'wellness', 'label' => 'Wellness', 'icon' => 'heart'],
        ['id' => 'policy', 'label' => 'GC Policies', 'icon' => 'gavel'],
    ];
@endphp
<div
    class="min-h-full w-full"
    x-data="{
        tab: '{{ $tab }}',
        setTab(id) {
            this.tab = id;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', id);
            history.replaceState({}, '', url);
        }
    }"
>
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start gap-3 min-w-0">
                <div class="rounded-lg p-2 shrink-0 bg-[#DCFCE7]">
                    <x-lucide-shield-check class="h-5 w-5 text-[#166534]" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Know Your Rights</p>
                    <h1 class="text-2xl font-bold text-gray-900 mt-0.5">GBV Awareness &amp; Rights Hub</h1>
                    <p class="text-sm text-gray-500 mt-1 max-w-lg leading-relaxed">
                        Philippine legal protections, reporting guidance, and victim support
                        for the Gordon College community.
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="setTab('reporting')"
                class="flex items-center justify-center gap-2 bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap shrink-0"
            >
                <x-lucide-file-text class="h-4 w-4" />How to Report
            </button>
        </div>
    </div>

    <div class="sticky top-0 z-30 -mx-6 border-b border-gray-200 bg-white dark:border-border dark:bg-background md:hidden">
        <div class="px-2">
            <div class="flex overflow-x-auto scrollbar-none">
                @foreach ($tabs as $item)
                    <button
                        type="button"
                        @click="setTab('{{ $item['id'] }}')"
                        :class="tab === '{{ $item['id'] }}' ? 'border-[#1D9E75] text-[#1D9E75]' : 'border-transparent text-gray-400 hover:text-gray-700 hover:bg-gray-50'"
                        class="flex items-center gap-2 px-4 py-3 text-sm font-semibold transition-all whitespace-nowrap border-b-2 flex-shrink-0"
                    >
                        <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-4 w-4 flex-shrink-0" />
                        {{ $item['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6 pt-4">
        <div class="space-y-6" x-show="tab === 'laws'" @if ($tab !== 'laws') x-cloak @endif>
            <div class="flex items-start gap-4 rounded-2xl p-5" style="background: var(--privacy-bg); border: 1px solid var(--card-outline)">
                <div class="w-10 h-10 rounded-xl bg-[#DCFCE7] flex items-center justify-center flex-shrink-0">
                    <x-lucide-info class="h-5 w-5 text-[#166534]" />
                </div>
                <div>
                    <p class="text-base font-bold text-[#14532D] mb-1">Understanding Your Legal Protections</p>
                    <p class="text-sm text-[#166534] leading-relaxed">
                        Philippine law provides multiple layers of protection for GBV and sexual harassment survivors.
                        These laws apply to all members of the Gordon College community.
                    </p>
                    <p class="text-xs text-[#14532D] font-medium mt-2">Last reviewed: {{ $reviewed }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                @foreach ($acts as $act)
                    <div :class="open ? 'md:col-span-3' : ''" x-data="{ open: false }">
                        <div class="rounded-2xl border-2 overflow-hidden transition-all flex flex-col bg-green-50 border-green-200">
                            <div class="p-5 flex flex-col">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 bg-green-100 text-[#1D9E75]">
                                        <x-dynamic-component :component="'lucide-'.$act['icon']" class="h-6 w-6" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full mb-1.5 bg-[#1D9E75] text-white">{{ $act['acronym'] }}</span>
                                        <h3 class="text-lg font-extrabold text-gray-900 leading-tight">{{ $act['raNumber'] }}</h3>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600 leading-relaxed mt-3">{{ $act['summary'] }}</p>
                                </div>
                                <button
                                    type="button"
                                    @click="open = !open"
                                    :aria-expanded="open.toString()"
                                    class="mt-4 inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold border-2 border-[#1D9E75] text-[#14532D] bg-white hover:bg-[#F0FDF4] transition-colors"
                                >
                                    <span x-show="!open" class="inline-flex items-center gap-2"><x-lucide-arrow-right class="h-4 w-4" />Read provisions &amp; penalties</span>
                                    <span x-show="open" x-cloak class="inline-flex items-center gap-2"><x-lucide-chevron-down class="h-4 w-4" />Show less</span>
                                </button>
                            </div>
                            <div x-show="open" x-cloak class="border-t-2 border-white/50 px-5 py-5 space-y-4 bg-white/60">
                                <div class="flex items-start gap-3 bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                                    <x-lucide-users class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Who It Protects</p>
                                        <p class="text-sm text-gray-700 leading-relaxed">{{ $act['who'] }}</p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Key Provisions</p>
                                    <div class="space-y-2">
                                        @foreach ($act['provisions'] as $provision)
                                            <div class="flex items-start gap-3 bg-white rounded-xl px-4 py-3 border border-gray-100 shadow-sm">
                                                <x-lucide-circle-check class="h-4 w-4 text-[#1D9E75] mt-0.5 flex-shrink-0" />
                                                <p class="text-sm text-gray-700 leading-relaxed">{{ $provision }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                                    <x-lucide-gavel class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs font-bold text-amber-600 uppercase tracking-wide mb-1">Penalties</p>
                                        <p class="text-sm text-amber-900 leading-relaxed">{{ $act['penalties'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6" x-show="tab === 'reporting'" @if ($tab !== 'reporting') x-cloak @endif>
            <div class="bg-[#F0FDF4] border-2 border-[#1D9E75]/25 rounded-2xl p-5">
                <p class="text-base font-bold text-[#178F65] mb-2">Step-by-Step Reporting Guide</p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Reporting can feel overwhelming. This guide walks you through each stage so you know what to expect. You are supported at every step.
                </p>
            </div>
            <div class="relative isolate">
                <div class="absolute left-5 top-8 bottom-8 w-0.5 bg-gradient-to-b from-[#1D9E75] to-[#1D9E75]/10 pointer-events-none -z-10"></div>
                <div class="space-y-4">
                    @foreach ($steps as $step)
                        <div class="flex items-start gap-4">
                            <div class="mt-5 w-10 h-10 rounded-full bg-[#1D9E75] text-white flex items-center justify-center flex-shrink-0 font-extrabold text-sm shadow-md ring-4 ring-white">
                                {{ $step['step'] }}
                            </div>
                            <div class="flex-1 min-w-0 bg-white border-2 border-gray-100 rounded-2xl p-5 hover:border-[#1D9E75]/20 transition-colors">
                                <h3 class="text-base font-bold text-gray-900 mb-2 leading-snug">{{ $step['title'] }}</h3>
                                <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $step['description'] }}</p>
                                <div class="bg-gray-50 rounded-xl p-4 space-y-2.5">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">What to do</p>
                                    @foreach ($step['tips'] as $tip)
                                        <div class="flex items-start gap-3">
                                            <x-lucide-circle-check class="h-4 w-4 text-[#1D9E75] mt-0.5 flex-shrink-0" />
                                            <p class="text-sm text-gray-700 leading-relaxed">{{ $tip }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <a href="/complaints/new" class="w-full flex items-center justify-center gap-3 bg-[#1D9E75] hover:bg-[#178F65] text-white text-base font-bold py-4 rounded-2xl transition-colors shadow-md">
                <x-lucide-file-text class="h-5 w-5" />File a Formal Complaint Now<x-lucide-arrow-right class="h-5 w-5" />
            </a>
        </div>

        <div class="space-y-6" x-show="tab === 'faq'" @if ($tab !== 'faq') x-cloak @endif>
            <div class="bg-[#F0FDF4] border-2 border-[#1D9E75]/25 rounded-2xl p-5">
                <p class="text-base font-bold text-[#178F65] mb-2">Frequently Asked Questions</p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Common questions about GBV, harassment, the complaint process, and your rights under Philippine law and Gordon College policy.
                </p>
            </div>
            <div class="space-y-3">
                @foreach ($faqs as $i => $item)
                    <div x-data="{ open: false }" class="bg-white border-2 rounded-2xl overflow-hidden transition-all" :class="open ? 'border-[#1D9E75]/40 shadow-sm' : 'border-gray-100 hover:border-gray-200'">
                        <button type="button" @click="open = !open" class="w-full flex items-start justify-between gap-4 px-5 py-4 text-left">
                            <div class="flex items-start gap-3 flex-1">
                                <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold mt-0.5 transition-colors" :class="open ? 'bg-[#1D9E75] text-white' : 'bg-gray-100 text-gray-500'">{{ $i + 1 }}</span>
                                <span class="text-sm font-semibold text-gray-800 leading-snug">{{ $item['q'] }}</span>
                            </div>
                            <x-lucide-chevron-down class="h-5 w-5 flex-shrink-0 mt-0.5 transition-transform" x-bind:class="open ? 'text-[#1D9E75] rotate-180' : 'text-gray-400'" />
                        </button>
                        <div x-show="open" x-cloak class="px-5 pb-5">
                            <div class="ml-10">
                                <p class="text-sm text-gray-600 leading-relaxed">{{ $item['a'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="bg-white border-2 border-gray-100 rounded-2xl p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#F0FDF4] flex items-center justify-center flex-shrink-0">
                    <x-lucide-message-circle class="h-5 w-5 text-[#1D9E75]" />
                </div>
                <div>
                    <p class="text-base font-bold text-gray-800 mb-1">Still have questions?</p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Contact the DEIU office directly through SpeakUp GC or use the AI Assistant (Laya) for guided support.
                        The Guidance &amp; Counseling Office is also available for personal assistance.
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6" x-show="tab === 'wellness'" @if ($tab !== 'wellness') x-cloak @endif>
            <div class="bg-rose-50 border-2 border-rose-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <x-lucide-heart class="h-6 w-6 text-rose-600" />
                </div>
                <div>
                    <p class="text-base font-bold text-rose-800 mb-1">You Are Not Alone</p>
                    <p class="text-sm text-rose-700 leading-relaxed">
                        Healing from GBV or harassment takes time. Gordon College is committed to providing holistic support —
                        regardless of whether you choose to file a formal complaint.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($wellness as $resource)
                    <div class="rounded-2xl border-2 p-5 space-y-3 bg-green-50 border-green-200">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center border-2 bg-green-50 border-green-200">
                            <x-dynamic-component :component="'lucide-'.$resource['icon']" class="h-5 w-5 text-[#1D9E75]" />
                        </div>
                        <h3 class="text-base font-bold text-gray-900">{{ $resource['title'] }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $resource['description'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="bg-[#F0FDF4] border-2 border-[#1D9E75]/30 rounded-2xl p-6 text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-[#1D9E75]/10 flex items-center justify-center mx-auto">
                    <x-lucide-shield-check class="h-7 w-7 text-[#1D9E75]" />
                </div>
                <p class="text-base font-extrabold text-[#178F65]">Anti-Retaliation Guarantee</p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Under <strong>RA 11313</strong> and Gordon College policy, any form of retaliation against you
                    for reporting, assisting in a complaint, or accessing support is strictly prohibited and independently actionable.
                </p>
                <a href="/complaints/new" class="inline-flex items-center gap-2 bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                    Report Retaliation <x-lucide-arrow-right class="h-4 w-4" />
                </a>
            </div>
        </div>

        <div class="space-y-6" x-show="tab === 'policy'" @if ($tab !== 'policy') x-cloak @endif>
            <div class="bg-[#F0FDF4] border-2 border-[#1D9E75]/25 rounded-2xl p-5">
                <p class="text-base font-bold text-[#178F65] mb-2">Gordon College Institutional Policies</p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    These policies govern how Gordon College handles GBV and harassment cases, protects complainants,
                    and ensures due process for all parties — aligned with applicable Philippine laws.
                </p>
            </div>
            @foreach ($policies as $policy)
                <div x-data="{ open: false }" class="bg-white border-2 rounded-2xl overflow-hidden transition-all" :class="open ? 'border-[#1D9E75]/40 shadow-sm' : 'border-gray-100'">
                    <div class="p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-[#F0FDF4] border border-[#1D9E75]/20 flex items-center justify-center flex-shrink-0">
                                <x-dynamic-component :component="'lucide-'.$policy['icon']" class="h-6 w-6 text-[#1D9E75]" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-[#1D9E75] uppercase tracking-wide mb-0.5">{{ $policy['subtitle'] }}</p>
                                <h3 class="text-base font-bold text-gray-900 mb-2">{{ $policy['title'] }}</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">{{ $policy['description'] }}</p>
                            </div>
                        </div>
                        <button type="button" @click="open = !open" class="mt-4 flex items-center justify-center gap-2 text-sm font-semibold text-[#1D9E75] bg-[#F0FDF4] hover:bg-green-100 transition-colors px-4 py-2.5 rounded-xl w-full">
                            <x-lucide-chevron-down class="h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                            <span x-text="open ? 'Show less' : 'View details'">View details</span>
                        </button>
                    </div>
                    <div x-show="open" x-cloak class="border-t border-gray-100 bg-gray-50 px-5 py-4 space-y-2">
                        @foreach ($policy['details'] as $detail)
                            <div class="flex items-start gap-3 bg-white rounded-xl px-4 py-3 border border-gray-100">
                                <x-lucide-circle-check class="h-4 w-4 text-[#1D9E75] mt-0.5 flex-shrink-0" />
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $detail }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <x-lucide-info class="h-5 w-5 text-amber-600" />
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-800 mb-1">Get the Full Policy Documents</p>
                    <p class="text-sm text-amber-700 leading-relaxed">
                        For the full text of the GC CODI, Anti-Sexual Harassment Policy, or Data Privacy Policy,
                        contact the DEIU office or the Office of the Registrar. Printed copies are available upon request.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="px-5 py-10 mt-8">
        <div class="max-w-5xl mx-auto text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-white/90 border-2 border-[#1D9E75]/30 flex items-center justify-center mx-auto shadow-sm">
                <x-lucide-shield-check class="h-8 w-8 text-[#1D9E75]" />
            </div>
            <h2 class="text-xl font-extrabold text-gray-900">Ready to Speak Up?</h2>
            <p class="text-sm text-gray-600 max-w-sm mx-auto leading-relaxed">
                You have the right to a safe, respectful environment. The DEIU is here to listen, support, and act —
                with full confidentiality and care.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="/complaints/new" class="flex items-center justify-center gap-2 bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-bold px-7 py-3.5 rounded-xl transition-colors shadow-md">
                    <x-lucide-file-text class="h-4 w-4" />File a Complaint
                </a>
                <a href="/complaints" class="flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold px-7 py-3.5 rounded-xl border-2 border-gray-200 transition-colors shadow-sm">
                    Track My Cases
                </a>
            </div>
            <p class="text-xs text-gray-500 pt-2">
                Educational guidance only — not a substitute for legal advice. Last reviewed {{ $reviewed }}.
            </p>
        </div>
    </div>
</div>
</x-layouts.complainant>
