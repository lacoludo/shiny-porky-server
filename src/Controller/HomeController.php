<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TransactionType;

class HomeController extends AbstractController
{
    /**
     * @Route("/users", name="app_users")
     */
    public function users()
    {

        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/shiny-porky-99246-firebase-adminsdk-8lwno-2262fcb9fd.json');
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        
        $database = $firebase->getDatabase();
        $reference = $database->getReference('/users');
        $snapshot = $reference->getSnapshot();

        $value = $snapshot->getValue();    
        
        return $this->render('users/index.html.twig', [
            'users' => $value,
        ]);
    }

    /**
     * @Route("/users/{id}/transactions", name="app_users_transactions")
     */
    public function userTransaction($id)
    {

        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/shiny-porky-99246-firebase-adminsdk-8lwno-2262fcb9fd.json');
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        
        $database = $firebase->getDatabase();
        $reference = $database->getReference('/porkies/' . $id);
        $snapshot = $reference->getSnapshot();

        $allTransactions = [];

        $porkies = $snapshot->getValue();    
        foreach ($porkies as $key => $porky) {
            if (isset($porky['transactions'])) {
                $transactions = $porky['transactions'];
                foreach($transactions as $keyTransac => $transaction) {
                    $transaction['porky'] = $key;
                    $transaction['id'] = $keyTransac;
                    $allTransactions[] = $transaction;
                }
            }
        }

        return $this->render('users/transactions.html.twig', [
            'transactions' => $allTransactions,
        ]);
    }

    /**
     * @Route("users/{user}/porky/{porky}/transaction/{transaction}/edit", name="app_transaction_edit")
     */
    public function transactionEdit($user, $porky, $transaction, Request $request)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/shiny-porky-99246-firebase-adminsdk-8lwno-2262fcb9fd.json');
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        
        $database = $firebase->getDatabase();
        $reference = $database->getReference('/porkies/' . $user . '/' . $porky . '/transactions/' . $transaction);
        $snapshot = $reference->getSnapshot();

        $transaction = $snapshot->getValue();    

        $form = $this->createForm(TransactionType::class);
        $form->get('status')->setData($transaction['status']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction['status'] = $form->getData()['status'];
            $reference->update($transaction);

            $referenceNotif = $database->getReference('/users/' . $user . '/notifications');
            
            $time = new \DateTime();

            $notif = [
                'content' => $form->getData()['status'],
                'date' => $time->getTimestamp(),
                'hasSeen' => false,
                'porky' => $porky,
                'transaction' => $transaction,
            ];

            $newNotifKey = $referenceNotif->push($notif);
        }

        return $this->render('users/edit.html.twig', [
            'form' => $form->createView(),
            'transaction' => $transaction,
        ]);
    }
}