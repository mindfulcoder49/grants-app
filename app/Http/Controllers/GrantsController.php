<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class GrantsController extends Controller
{
    public function search(Request $request) {
        // This should process the search query and fetch relevant grants from a data source.
        // For demonstration, we're using enhanced dummy data with more segmented information.

        $results = [
            [
                'id' => 1,
                'title' => 'NSF SBIR - AI Focused Grants',
                'description' => 'Funding for AI projects that aim to address national priorities, with a focus on commercialization potential.',
                'amount' => 'Up to $2 million',
                'deadline' => '2024-11-22',
                'match' => 95,
                'purpose' => 'To support small businesses engaging in scientific research and development that has the potential for commercialization.',
                'eligibility' => 'Small businesses with fewer than 500 employees, at least 50% owned by U.S. citizens or permanent residents.',
                'application_process' => 'Initial Project Pitch followed by Full Proposal if selected.',
                'contact' => 'nsf-sbir@nsf.gov',
                'website' => 'https://seedfund.nsf.gov/topics/artificial-intelligence/',
            ],
            [
                'id' => 2,
                'title' => 'Meta Llama 3.1 Impact Grants',
                'description' => 'Grants for leveraging Llama 3.1 models to address community challenges.',
                'amount' => 'Up to $500,000',
                'deadline' => '2024-11-22',
                'match' => 85,
                'purpose' => 'To foster the use of Llama 3.1 models in impactful community projects across various sectors such as education, public services, and economic development.',
                'eligibility' => 'Non-profits, academic institutions, and businesses with innovative AI application ideas.',
                'application_process' => 'Submission of a detailed proposal through the Meta platform.',
                'contact' => 'LlamaImpactEvent@meta.com',
                'website' => 'https://llama.meta.com/llama-impact-grants/',
            ],
            [
                'id' => 3,
                'title' => 'Climate Change AI Innovation Grants 2024',
                'description' => 'Funding for AI projects aimed at climate change mitigation and adaptation.',
                'amount' => 'Up to $150,000',
                'deadline' => '2024-09-15',
                'match' => 80,
                'purpose' => 'To support innovative AI projects that address climate change, particularly those that develop new datasets or computational models.',
                'eligibility' => 'Researchers affiliated with accredited universities in OECD Member Countries.',
                'application_process' => 'Proposals submitted via the CMT submission portal.',
                'contact' => 'grants@climatechange.ai',
                'website' => 'https://www.climatechange.ai/calls/innovation_grants_2024',
            ],
        ];

        return Inertia::render('Results', ['grants' => $results]);
    }
}
